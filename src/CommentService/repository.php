<?php

class CommentRepository extends BasePdoRepository {

	private $fieldNames = 'entity_type, entity_id, user_id, created, answer_to, comment';

	
	/**
	 * gets all comments of an entity
	 * @param EntityTypes $entityType
	 * @param int $entityId
	 * @return Comment[]
	 */
	public function getEntityComments($entityType, $entityId) {
		$query = 'SELECT comment_id, ' . $this->fieldNames . ' FROM comments WHERE entity_type = ? and entity_id = ? ORDER BY created desc';
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($entityType, $entityId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Comment::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * creates comment
	 * @param Comment $comment
	 */
	public function createComment(Comment $model) {
		$query = "INSERT INTO comments (
		entity_type
		, entity_id
		, user_id
		, created
		, comment
		, answer_to
		) VALUES (
		?, ?, ?, ?, ?, ?
		)";
		$stmt = $this->prepare($query);
		$parameters = array(
			$model->entityType
			, $model->entityId
			, $model->userId
			, $model->created
			, $model->comment
			, $model->answerTo
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * gets comment by id
	 * @param int $commentId
	 * @return Comment
	 */
	public function getCommentById($commentId) {
		$query = "SELECT comment_id, ".$this->fieldNames." FROM comments where comment_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($commentId));
		
		if ($a = $stmt->fetch()) {
			$model = Comment::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	
	/**
	 * deletes a comment
	 * @param unknown $commentId
	 */
	public function deleteComment($commentId) {
		$query = "DELETE from comments where comment_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($commentId));
	}
}