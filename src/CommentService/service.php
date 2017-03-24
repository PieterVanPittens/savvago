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
	 * constructor
	 * @param User $contextUser
	 * @param CommentManager $manager
	 * @param EntityStatsManager $entityStatsManager
	 * @param Array $settings
	 */
	function __construct($contextUser, $manager, $entityStatsManager, $settings) {
		$this->contextUser = $contextUser;
		$this->manager = $manager;
		$this->entityStatsManager = $entityStatsManager;
		$this->settings = $settings;
	}

	/**
	 * gets all comments of a lesson
	 * @param int $lessonId
	 * @return Comment[]
	 */
	public function getLessonComments($lessonId) {
		$comments = $this->manager->getEntityComments(EntityTypes::Lesson, $lessonId);
		return $comments;
	}
	
	/**
	 * comments a lesson
	 * @param int $lessonId
	 * @param string $commentText
	 */
	public function commentLesson($lessonId, $commentText) {
		$comment = new Comment();
		$comment->userId = $this->contextUser->userId;
		$comment->comment = $commentText;
		$comment->created = time();
		$comment->entityType = EntityTypes::Lesson;
		$comment->entityId = $lessonId;
		
		$this->transactionManager->start();
		$this->manager->createComment($comment);
		$this->entityStatsManager->increaseEntityStat(EntityTypes::Lesson, $lessonId, EntityStats::numComments, 1);
		
		$this->transactionManager->commit();
		$apiResult = ApiResultFactory::createSuccess('commented', $comment);
		return $apiResult;
	}
}