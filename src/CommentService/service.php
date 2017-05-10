<?php
/**
 * CommentService
*/
class CommentService extends BaseService {

	/**
	 * ContextUser
	 * @var User
	 */
	private $contextUser;

	/**
	 * @var CommentManager
	 */
	private $manager;
	
	/**
	 * settings
	 * @var Array
	 */
	private $settings;

	/**
	 * EntityStatsManager
	 * @var EntityStatsManager
	 */
	private $entityStatsManager;

	/**
	 * UserManager
	 * @var UserManager
	 */
	private $userManager;
	
	/**
	 * constructor
	 * @param User $contextUser
	 * @param CommentManager $manager
	 * @param EntityStatsManager $entityStatsManager
	 * @param UserManager $userManager
	 * @param Array $settings
	 */
	function __construct(
			$contextUser
			, $manager
			, $entityStatsManager
			, $userManager
			, $settings) {
		$this->contextUser = $contextUser;
		$this->manager = $manager;
		$this->entityStatsManager = $entityStatsManager;
		$this->userManager = $userManager;
		$this->settings = $settings;
	}

	/**
	 * gets all comments of a lesson
	 * @param int $lessonId
	 * @return Comment[]
	 */
	public function getLessonComments($lessonId) {
		return $this->getEntityComments(EntityTypes::Lesson, $lessonId);
	}

	/**
	 * gets all comments of a journey
	 * @param int $journeyId
	 * @return Comment[]
	 */
	public function getJourneyComments($journeyId) {
		return $this->getEntityComments(EntityTypes::Journey, $journeyId);
	}
	
	private function getEntityComments($entityType, $entityId) {
		$comments = $this->manager->getEntityComments($entityType, $entityId);
		foreach($comments as $comment) {
			$comment->user = $this->userManager->getUserById($comment->userId);
		}
		return $comments;		
	}
	
	/**
	 * comments a lesson
	 * @param int $lessonId
	 * @param string $commentText
	 */
	public function commentLesson($lessonId, $commentText) {
		return $this->commentEntity(EntityTypes::Lesson, $lessonId, $commentText);
	}

	/**
	 * comments a journey
	 * @param int $journeyId
	 * @param string $commentText
	 */
	public function commentJourney($journeyId, $commentText) {
		return $this->commentEntity(EntityTypes::Journey, $journeyId, $commentText);
	}
	
	public function commentEntity($entityType, $entityId, $commentText) {
		$comment = new Comment();
		$comment->userId = $this->contextUser->userId;
		$comment->comment = $commentText;
		$comment->created = time();
		$comment->entityType = $entityType;
		$comment->entityId = $entityId;
		
		$this->transactionManager->start();
		$this->manager->createComment($comment);
		$this->entityStatsManager->increaseEntityStat($entityType, $entityId, EntityStats::numComments, 1);
		
		$this->transactionManager->commit();
		$apiResult = ApiResultFactory::createSuccess('commented', $comment);
		return $apiResult;
	}
	

}