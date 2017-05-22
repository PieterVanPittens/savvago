<?php
/**
 * Journey Service
 */
class JourneyService extends BaseService {

	/**
	 * ContextUser
	 * @var User
	 */
	private $contextUser;

	/**
	 * @var JourneyManager
	 */
	private $manager;

	/**
	 * @var ServiceCacheManager
	 */
	private $serviceCacheManager;

	/**
	 * @var TagMatchingManager
	 */
	private $tagMatchingManager;
	
	/**
	 * @var TagManager
	 */
	private $tagManager;

	/**
	 * @var userManager
	 */
	private $userManager;
	
	/**
	 * settings
	 * @var Array
	 */
	private $settings;

	/**
	 * @var EntityStatsManager
	 */
	private $entityStatsManager;
	
	
	/**
	 * constructor
	 * @param User $contextUser
	 * @param JourneyManager $manager
	 * @param ServiceCacheManager $serviceCacheManager
	 * @param TagManager $tagManager
	 * @param TagMatchingManager $tagMatchingManager
	 * @param EntityStatsManager $entityStatsManager
	 * @param UserManager $userManager
	 * @param Array $settings
	 */
	function __construct(
			$contextUser
			, $manager
			, $serviceCacheManager
			, $tagManager
			, $tagMatchingManager
			, $entityStatsManager
			, $userManager
			, $settings) {
		$this->contextUser = $contextUser;
		$this->manager = $manager;
		$this->serviceCacheManager = $serviceCacheManager;
		$this->tagManager = $tagManager;
		$this->tagMatchingManager = $tagMatchingManager;
		$this->entityStatsManager = $entityStatsManager;
		$this->userManager = $userManager;
		$this->settings = $settings;
	}
	
	/**
	 * gets all journeys
	 * @return Journey[]
	 */
	public function getJourneys() {
		// todo: securitycheck: only guides
		$journeys = $this->manager->getJourneys();
		return $journeys;
	}
	
	/**
	 * gets one journey
	 * @param int $journeyId
	 * @return Journey
	 */
	public function getJourney($journeyId) {
		// todo: securitycheck: only guides
		$journey = $this->manager->getJourney($journeyId);
		return $journey;
	}

	/**
	 * gets one journey by name
	 * @param int $name
	 * @return Journey
	 */
	public function getJourneyByName($name) {
		// todo: securitycheck: only guides

		$journey = $this->manager->getJourneyByName($name);
		$parsedown = new Parsedown();
		$journey->descriptionHtml = $parsedown->text($journey->description);
		
		$journey->stats = $this->entityStatsManager->getEntityStats(EntityTypes::Journey, $journey->journeyId);
		$journey->user = $this->userManager->getUserById($journey->userId);

		// log view of this journey
		$this->entityStatsManager->increaseEntityStat(EntityTypes::Journey, $journey->journeyId, EntityStats::numViews, 1);
				
		$this->addJourneyUrls($journey);
		return $journey;
	}
	
	/**
	 * updates journey
	 * @param int $journeyId
	 * @param object $input
	 */
	public function updateJourney($journeyId, $input) {
		// todo: security checks
		
		
		$journey = $this->manager->getJourney($journeyId);
		if ($journey == null) {
			throw new NotFoundException("Journey does not exist");
		}
		// update title/name, isactive
		$journey->isActive = $input->isActive == 1 ? true: false;
		
		// update tags
		$this->manager->updateJourney($journey);

		$apiResult = ApiResultFactory::createSuccess("Journey updated", $journey);
		return $apiResult;
	}

	/**
	 * creates journey
	 * @param Object $lesson
	 * @return ApiResult
	 */
	public function createJourney($input) {
		// todo: security checks

		$hasError = false;
		$apiResult = new ApiResult();
		$apiResult->message = new Message();
		$apiResult->message->type = MessageTypes::Success;

		if (!isset($input->title)) {
			$apiResult->message->AddPropertyMessage('title', 'Give it a name');
			$apiResult->message->type = MessageTypes::Error;
		}
		if (!isset($input->description) || (is_null($input->description)) || ($input->description == "")) {
			$apiResult->message->AddPropertyMessage('description', 'Enter a description');
			$apiResult->message->type = MessageTypes::Error;
		}
		if (!isset($input->tags)) {
			$apiResult->message->AddPropertyMessage('tags', 'Provide at least one tag');
			$apiResult->message->type = MessageTypes::Error;
		}
		if ($apiResult->message->type == MessageTypes::Error) {
			return $apiResult;
		}
		
		
		// validate and sanitize
		$journey = new Journey();
		$journey->title = $input->title;
		$journey->tags = $input->tags;
		$journey->description = $input->description;
		$journey->isActive = false;
		$journey->numEnrollments = 0;
		$journey->numStations = 0;
		$journey->created = time();
		// start transaction
		$this->transactionManager->start();
		// create lesson
		$journey->userId = $this->contextUser->userId;
		$apiResult = $this->manager->createJourney($journey);
		if ($apiResult->message->type != MessageTypes::Success) {
			return $apiResult;
		}
	
		// add tags
		
		try {
			$tags = $this->tagManager->saveTags($input->tags);
		} catch (ValidationException $ex) {
			$apiResult = ApiResultFactory::createErrorFromValidationException($ex);
			return $apiResult;
		}
		
		
		
		// assign tags
		$this->tagManager->assignTagsToEntity($tags, $apiResult->object);
	
		// assign lessons based on matched tags 
		$lessons = $this->tagMatchingManager->getMatchingLessons($tags);		
		$this->manager->assignLessonsToJourney($journey, $lessons);
		
		
		// commit
		$this->transactionManager->commit();
	
		return $apiResult;
	}


	/**
	 * deletes a journey
	 * @param int $journeyId
	 * @return ApiResult
	 */
	public function deleteJourney($journeyId) {
		// todo: security check

		// journey exists?
		$journey = $this->manager->getJourney($journeyId);
		if ($journey == null) {
			throw new NotFoundException("Journey does not exist");
		}
		
		if ($journey->isActive) {
			$apiResult = ApiResultFactory::createError("Deactivate Journey first", null);
			return $apiResult;
		}
		
		// todo: check enrollments(travellers)
		
		
		// delete
		$this->transactionManager->start();
		
		$this->manager->deleteJourneyLessons($journeyId);
		$this->tagManager->deleteEntityTags(EntityTypes::Journey, $journeyId);
		$this->manager->deleteJourney($journeyId);

		$this->transactionManager->commit();
		
		
		$apiResult = ApiResultFactory::createSuccess("Journey deleted", null);
		return $apiResult;
	}
	
	/**
	 * gets list of journeys that match a list of tags
	 * journey needs to have all of these tags attached
	 * @param string $tagsString
	 */
	public function getMatchingJourneys($tagsString) {
		// todo: security check
	
		$tagNames = $this->tagManager->splitTagNames($tagsString);
		$tags = $this->tagManager->getTagsByNames($tagNames);
		$journeys = array();
		if (count($tags) < count($tagNames)) {
			// not all tags are known yet -> it is not possible that there is any matching journey
		} else {
			$journeys = $this->tagMatchingManager->getMatchingJourneys($tags);
			$this->addJourneysUrls($journeys);
		}
		return $journeys;
	}
	
	/**
	 * adds Urls to Journeys
	 * @param Journey[] $journeys
	 */
	private function addJourneysUrls($journeys) {
		foreach($journeys as $journey) {
			$this->addJourneyUrls($journey);
		}
	}
	
	/**
	 * adds Urls to Journey
	 * @param Journey $journeys
	 */
	private function addJourneyUrls(Journey $journey) {
		$journey->urls = array();
		$journey->urls['view'] = $this->settings['application']['base'] . 'journeys/' . $journey->name;
		$journey->urls['images']['tile'] = $journey->name;
		$journey->urls['check'] = $this->settings['application']['api'] . 'journeys/' . $journey->journeyId . '/check';
		$journey->urls['like'] = $this->settings['application']['api'] . 'journeys/' . $journey->journeyId . '/like';
		$journey->urls['lessons'] = $this->settings['application']['api'] . 'journeys/' . $journey->journeyId . '/lessons';
		$journey->urls['comments'] = $this->settings['application']['api'] . 'journeys/' . $journey->journeyId . '/comments';
		$journey->urls['deleteComment'] = $this->settings['application']['api'] . 'comments';
	}

	/**
	 * gets top n courses based on num enrollments
	 * @param int $n
	 * @return Journey[]
	 */
	public function getTopNJourneys($n) {
		// todo: security check
		$journeys = $this->manager->getTopNJourneys($this->contextUser->userId, $n);
		$this->addJourneysUrls($journeys);
		foreach($journeys as $journey) {
			$journey->stats = $this->entityStatsManager->getEntityStats(EntityTypes::Journey, $journey->journeyId);
		}
		return $journeys;
	}
}
