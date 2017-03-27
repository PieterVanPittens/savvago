<?php

/**
 * Lesson Service
*/
class LessonService extends BaseService {

	/**
	 * ContextUser
	 * @var User
	 */
	private $contextUser;

	/**
	 * @var LessonManager
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
	 * @var UserManager
	 */
	private $userManager;

	/**
	 * @var EntityStatsManager
	 */
	private $entityStatsManager;
	
	/**
	 * @var iProviderPlugin
	 */
	private $storageProvider;
	
	/**
	 * @var ContentManager
	 */
	private $contentManager;
	

	/**
	 * settings
	 * @var array
	 */
	private $settings;
	
	/**
	 * constructor
	 * @param User $contextUser
	 * @param array $settings
	 * @param LessonManager $manager
	 * @param ServiceCacheManager $serviceCacheManager
	 * @param TagManager $tagManager
	 * @param TagMatchingManager $tagMatchingManager
	 * @param UserManager $userManager
	 * @param EntityStatsManager $entityStatsManager
	 * @param iProviderPlugin $storageProvider
	 * @param ContentManager $contentManager
	 */
	function __construct(
			$contextUser
			, $settings
			, $manager
			, $serviceCacheManager
			, $tagManager
			, $tagMatchingManager
			, $userManager
			, $entityStatsManager
			, $storageProvider
			, $contentManager
		) {
		$this->contextUser = $contextUser;
		$this->settings = $settings;
		$this->manager = $manager;
		$this->serviceCacheManager = $serviceCacheManager;
		$this->tagManager = $tagManager;
		$this->tagMatchingManager = $tagMatchingManager;
		$this->userManager = $userManager;
		$this->entityStatsManager = $entityStatsManager;
		$this->storageProvider = $storageProvider;
		$this->contentManager = $contentManager;
	}

	/**
	 * creates lesson
	 * @param Object $lesson
	 * @return ApiResult
	 */
	public function createLesson($input) {
		// todo: security checks

		$hasError = false;
		$apiResult = new ApiResult();
		$apiResult->message = new Message();
		$apiResult->message->type = MessageTypes::Success;
		$apiResult->message->text = "Correct all errors";
		
		if (!isset($input->title)) {
			$apiResult->message->AddPropertyMessage('title', 'Give it a name');
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
		$lesson = new Lesson();
		$lesson->title = $input->title;
		$lesson->tags = $input->tags;
		$lesson->isActive = false;
		$lesson->created = time();
		
		
		// start transaction
		$this->transactionManager->start();
		// create lesson
		$lesson->userId = $this->contextUser->userId;
		$apiResult = $this->manager->createLesson($lesson);
		if ($apiResult->message->type != MessageTypes::Success) {
			return $apiResult;
		}
		
		// add tags
		$tags = $this->tagManager->saveTags($input->tags);

		// assign tags
		$this->tagManager->assignTagsToEntity($tags, $apiResult->object);		
		
		// assign journeys based on matched tags
		$journeys = $this->tagMatchingManager->getMatchingJourneys($tags);
		$this->manager->assignJourneysToLesson($lesson, $journeys);
		
		
		// todo: content
		
		// commit
		$this->transactionManager->commit();
		
		return $apiResult;
	}
	
	/**
	 * gets one lesson
	 * @param int $lessonId
	 * @return Lesson
	 */
	public function getLesson($lessonId) {
		// todo: securitycheck: only guides
		$lesson = $this->manager->getLesson($lessonId);
		return $lesson;
	}

	/**
	 * gets lesson by name
	 * @param int $name
	 * @return Lesson
	 */
	public function getLessonByName($name) {
		// todo: securitycheck: only guides

		$lesson = $this->manager->getLessonByName($name);
		if ($lesson == null) {
			throw new NotFoundException("Lesson $name does not exist");
		}
		$this->addLessonUrls($lesson);
		$lesson->stats = $this->entityStatsManager->getEntityStats(EntityTypes::Lesson, $lesson->lessonId);
		$lesson->user = $this->userManager->getUserById($lesson->userId);
		$lesson->content = $this->contentManager->getContentObject($lesson->contentObjectId);
		$lesson->content->type = $this->contentManager->getContentType($lesson->content->typeId);
		if (is_null($lesson->content->type)) {
			throw new Exception('content type does not exist: '.$lesson->content->typeId);
		}
		// log view of this lesson
		$this->entityStatsManager->increaseEntityStat(EntityTypes::Lesson, $lesson->lessonId, EntityStats::numViews, 1);
		
		return $lesson;
	}
	
	
	/**
	 * updates lesson
	 * @param int $lessonId
	 * @param object $input
	 */
	public function updateLesson($lessonId, $input) {
		// todo: security checks
	
	
		$lesson = $this->manager->getLesson($lessonId);
		if ($lesson == null) {
			throw new NotFoundException("Lesson does not exist");
		}
		if ($input->isActive == 1) {
			// activation only possible if lesson has content
			if (is_null($lesson->contentObjectId)) {
				$apiResult = ApiResultFactory::createError("Add Content to this lesson first", null);
				return $apiResult;
			}
		}
		
		// update title/name, isactive
		$lesson->isActive = $input->isActive == 1 ? true: false;
	
		$lesson->title = $input->title;
		$lesson->tags = $input->tags;
		
		// update tags
		$this->manager->updateLesson($lesson);
	
		$apiResult = ApiResultFactory::createSuccess("Lesson updated", $lesson);
		return $apiResult;
	}
	
	
	/**
	 * gets list of lessons that match a list of tags
	 * lesson needs to have all of these tags attached
	 * @param string $tagsString
	 */
	public function getMatchingLessons($tagsString) {
		// todo: security check
	
		$tagNames = $this->tagManager->splitTagNames($tagsString);
		$tags = $this->tagManager->getTagsByNames($tagNames);
		$lessons = array();
		if (count($tags) < count($tagNames)) {
			// not all tags are known yet -> it is not possible that there is any matching journey
		} else {
			$lessons = $this->tagMatchingManager->getMatchingLessons($tags);
			$this->addLessonsUrls($lessons);
		}
		return $lessons;
	}
	
	/**
	 * adds Urls to Lessons
	 * @param Lesson[] $lessons
	 */
	private function addLessonsUrls($lessons) {
		foreach($lessons as $lesson) {
			$this->addLessonUrls($lesson);
		}
	}

	/**
	 * adds Urls to Lesson
	 * @param Lesson $lessons
	 */
	private function addLessonUrls(Lesson $lesson) {
	
		$contentType = null;
		if ($lesson->contentObjectId !== null) {
			$content = $this->contentManager->getContentObject($lesson->contentObjectId);
			if ($content !== null) {
				$contentType = $this->contentManager->getContentType($content->typeId); // todo: service cache
			}				
		}
			
		$image = '';
		$thumbnail = '';
		if ($contentType !== null) {
			switch ($contentType->source) {
				case ContentSourceTypes::Link:
					
					$pluginName = $contentType->name;
					$plugin = PluginFactory::createContentPlugin($pluginName);
					
					$image = $plugin->getImageUrl($content);
					$thumbnail = $plugin->getThumbnailUrl($content);
					break;
				case ContentSourceTypes::Article:
					break;
				case ContentSourceTypes::Mediafile:
					$image = $this->storageProvider->getAssetUrl($lesson->image);
					$thumbnail = $this->storageProvider->getAssetUrl($lesson->image);
					break;
			}
		}
		
		$urls = array(
			'view' => $this->settings['application']['base'] . 'lessons/' . $lesson->name
			, 'check' => $this->settings['application']['api'] . 'lessons/' . $lesson->lessonId . '/check'
			, 'like' => $this->settings['application']['api'] . 'lessons/' . $lesson->lessonId . '/like'
			, 'image' => $image
			, 'thumbnail' => $thumbnail
		);
		$lesson->urls = $urls;
		
	}
	
	/**
	 * gets all lessons
	 * @return array
	 */
	public function getLessons() {
		$lessons = $this->manager->getLessons();
		$this->addLessonsUrls($lessons);
		return $lessons;
	}

	/**
	 * deletes a lesson
	 * @param int $lessonId
	 * @return ApiResult
	 */
	public function deleteLesson($lessonId) {
		// todo: security check
	
		// journey exists?
		$lesson = $this->manager->getLesson($lessonId);
		if ($lesson == null) {
			throw new NotFoundException("Lesson does not exist");
		}
	
		if ($lesson->isActive) {
			$apiResult = ApiResultFactory::createError("Deactivate Lesson first", null);
			return $apiResult;
		}
	
		// todo: check enrollments(travellers)
	
	
		// delete
		$this->transactionManager->start();
	
		$this->manager->deleteJourneyLessons($lessonId);
		$this->tagManager->deleteEntityTags(EntityTypes::Lesson, $lessonId);
		$this->manager->deleteLesson($lessonId);
		
		$this->transactionManager->commit();
	
		$apiResult = ApiResultFactory::createSuccess("Lesson deleted", null);
		return $apiResult;
	}
	
	/**
	 * gets all lessons of a journey
	 * @param int $journeyId
	 * @return Journey[]
	 */
	public function getJourneyLessons($journeyId) {
		$lessons = $this->manager->getJourneyLessons($journeyId);
		$this->addLessonsUrls($lessons);
		return $lessons;
	}
}
