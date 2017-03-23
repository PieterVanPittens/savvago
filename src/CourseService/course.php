<?php

/**
 * Course
 *
 */
class Course extends BaseModel implements iEntity {
	public $courseId;
	public $userId;
	public $universityId;
	public $name;
	public $urls = array();
	public $title;
	public $subtitle;
	public $description;
	public $imageName;
	public $categoryId;
	public $numSections;
	public $numLessons;
	public $numEnrollments;
	public $isPublished;
	public $uuid;

	public function setTitle($title) {
		$this->title = $title;
		$this->name = ModelHelper::convertTitleToName($title);
	}

	/**
	 * list of all course progresses of one user
	 */
	public $progresses = array();


	/**
	 * promo video id
	 */
	public $videoId;
	/**
	 * @var University;
	 */
	public $university;

	public $sections;

	/**
	 * @var Category
	 */
	public $category;

	/**
	 * parent category
	 * @var Category
	 */
	public $parentCategory;

	/**
	 * @var ContentObject
	 */
	public $video;

	/**
	 * @var User
	 */
	public $user;


	public function getEntityType() {
		return ModelTypes::Course;
	}
	public function getId() {
		return $this->courseId;
	}

}

?>