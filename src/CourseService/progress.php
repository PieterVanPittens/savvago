<?php
/**
 * progress a user made in a course, topic, learning objects
 */
class Progress extends BaseModel {
	public $userId;
	public $referenceId;
	public $courseId;
	public $timestamp;
	/**
	 * @var ProgressTypes
	 */
	public $type;
	public $value;

}
?>