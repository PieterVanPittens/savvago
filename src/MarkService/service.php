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
		$mark = new Mark();
		$mark->created = time();
		$mark->entityId = $lessonId;
		$mark->entityType = EntityTypes::Lesson;
		$mark->type = MarkTypes::Like;
		$mark->userId = $this->contextUser->userId;
		
		$this->transactionManager->start();
		
		$increase = $this->manager->mark($mark);
		// update num likes of lesson
		$this->entityStatsManager->increaseEntityStat(EntityTypes::Lesson, $lessonId, EntityStats::numLikes, $increase);
		
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
		$mark = new Mark();
		$mark->created = time();
		$mark->entityId = $lessonId;
		$mark->entityType = EntityTypes::Lesson;
		$mark->type = MarkTypes::Check;
		$mark->userId = $this->contextUser->userId;
	
		$this->transactionManager->start();
	
		$increase = $this->manager->mark($mark);
		// update num likes of lesson
		$this->entityStatsManager->increaseEntityStat(EntityTypes::Lesson, $lessonId, EntityStats::numStationChecks, $increase);
	
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
	
}