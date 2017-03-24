<?php

/**
 * CommentManager
*/
class CommentManager extends BaseManager {

	/**
	 * CommentRepository
	 * @var CommentRepository
	 */
	protected $repository;

	/**
	 * gets all comments of an entity
	 * @param EntityTypes $entityType
	 * @param int $entityId
	 * @return Comment[]
	 */
	public function getEntityComments($entityType, $entityId) {
		$comments = $this->repository->getEntityComments($entityType, $entityId);
		return $comments;
	}
	

	/**
	 * creates comment
	 * @param Comment $comment
	 */
	public function createComment(Comment $comment) {
		$this->repository->createComment($comment);
	}
}