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
	
	/**
	 * gets comment by id
	 * @param int $commentId
	 * @return Comment
	 */
	public function getCommentById($commentId) {
		$comment = $this->repository->getCommentById($commentId);
		return $comment;
	}
	
	/**
	 * deletes a comment
	 * @param unknown $commentId
	 */
	public function deleteComment($commentId) {
		$this->repository->deleteComment($commentId);
	}
}