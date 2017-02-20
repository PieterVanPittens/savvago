<?php
/**
 * enrollment
 *
 * which user is enrolled to which course
 */
class Enrollment extends BaseModel {
	public $courseId;
	public $userId;
	public $timestamp;
}

?>