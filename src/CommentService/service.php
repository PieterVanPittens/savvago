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
	 * constructor
	 * @param User $contextUser
	 * @param CommentManager $manager
	 * @param Array $settings
	 */
	function __construct($contextUser, $manager, $settings) {
		$this->contextUser = $contextUser;
		$this->manager = $manager;
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
}