<?php

/**
 * Comment
 *
 */
class Comment extends BaseModel implements iEntity {
	
	public $commentId;
	
	/**
	 * EntityType
	 * @var EntityTypes
	 */
	public $entityType;
	
	public $entityId;
	
	public $userId;
	public $user;
	
	public $created;
	
	public $comment;
	
	/**
	 * commentid that this is an answer to
	 * @var int
	 */
	public $answerTo;
	
	public function getId() {
		return $this->commentId;
	}
	
	public function getEntityType() {
		return EntityTypes::Comment;
	}
}