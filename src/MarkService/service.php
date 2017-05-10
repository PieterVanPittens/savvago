<?php
/**
 * MarkService
 */
class MarkService extends BaseManager {

	/**
	 * MarkManager
	 * @var MarkManager
	 */
	private $manager;
	
	/**
	 * ContextUser
	 * @var User
	 */
	private $contextUser;

	
	/**
	 * EntityStatsManager
	 * @var EntityStatsManager
	 */
	private $entityStatsManager;
	
	
	/**
	 * constructor
	 * @param User $contextUser
	 * @param MarkManager $manager
	 * @param EntityStatsManager $entityStatsManager
	 */
	function __construct($contextUser, $manager, $entityStatsManager) {
		$this->contextUser = $contextUser;
		$this->manager = $manager;
		$this->entityStatsManager = $entityStatsManager;
	}
	

	/**
	 * likes a lesson
	 * @param int $lessonId
	 * @return ApiResult
	 */
	public function likeLesson($lessonId) {
		return $this->likeEntity(EntityTypes::Lesson, $lessonId);
	}

	/**
	 * likes a journey
	 * @param int $journeyId
	 * @return ApiResult
	 */
	public function likeJourney($journeyId) {
		return $this->likeEntity(EntityTypes::Journey, $journeyId);
	}
	
	
	private function likeEntity($entityType, $entityId) {
		$mark = new Mark();
		$mark->created = time();
		$mark->entityId = $entityId;
		$mark->entityType = $entityType;
		$mark->type = MarkTypes::Like;
		$mark->userId = $this->contextUser->userId;
		
		$this->transactionManager->start();
		
		$increase = $this->manager->mark($mark);
		// update num likes of lesson
		$this->entityStatsManager->increaseEntityStat($entityType, $entityId, EntityStats::numLikes, $increase);
		
		$this->transactionManager->commit();
		if ($increase == 1) {
			$apiResult = ApiResultFactory::createSuccess("liked", null);
		} else {
			$apiResult = ApiResultFactory::createSuccess("disliked", null);
		}
		return $apiResult;
	}
	
	
	
	/**
	 * checks a lesson
	 * @param int $lessonId
	 * @return ApiResult
	 */
	public function checkLesson($lessonId) {
		return $this->checkEntity(EntityTypes::Lesson, $lessonId);
	}
	
	/**
	 * checks a journey
	 * @param int $journeyId
	 * @return ApiResult
	 */
	public function checkJourney($journeyId) {
		return $this->checkEntity(EntityTypes::Journey, $journeyId);
	}
	
	private function checkEntity($entityType, $entityId) {
		$mark = new Mark();
		$mark->created = time();
		$mark->entityId = $entityId;
		$mark->entityType = $entityType;
		$mark->type = MarkTypes::Check;
		$mark->userId = $this->contextUser->userId;
		
		$this->transactionManager->start();
		
		$increase = $this->manager->mark($mark);
		// update num likes of lesson
		$this->entityStatsManager->increaseEntityStat($entityType, $entityId, EntityStats::numStationChecks, $increase);
		
		$this->transactionManager->commit();
		if ($increase == 1) {
			$apiResult = ApiResultFactory::createSuccess("checked", null);
		} else {
			$apiResult = ApiResultFactory::createSuccess("unchecked", null);
		}
		return $apiResult;
	}
	
	/**
	 * checks if contextuser has this lesson checked
	 * @param int $lessonId
	 * @return bool
	 */
	public function isLessonChecked($lessonId) {
		return $this->manager->marksEntity($this->contextUser->userId, EntityTypes::Lesson, $lessonId, MarkTypes::Check);
	}

	/**
	 * checks if contextuser likes this lesson
	 * @param int $lessonId
	 * @return bool
	 */
	public function likesLesson($lessonId) {
		return $this->manager->marksEntity($this->contextUser->userId, EntityTypes::Lesson, $lessonId, MarkTypes::Like);
	}
	
	/**
	 * checks if contextuser has this journey checked
	 * @param int $journeyId
	 * @return bool
	 */
	public function isJourneyChecked($journeyId) {
		return $this->manager->marksEntity($this->contextUser->userId, EntityTypes::Journey, $journeyId, MarkTypes::Check);
	}
	
	/**
	 * checks if contextuser likes this journey
	 * @param int $journeyId
	 * @return bool
	 */
	public function likesJourney($journeyId) {
		return $this->manager->marksEntity($this->contextUser->userId, EntityTypes::Journey, $journeyId, MarkTypes::Like);
	}
	
}