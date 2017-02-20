<?php

/**
 * Section
 *
 */
class Section extends BaseModel {
	public $sectionId;
	public $name;
	public $courseId;
	public $rank;
	/**
	 * description in markdown
	 */
	public $description;
	/**
	 * description parsed
	 */
	public $descriptionHtml;

	public $numLessons;

	public $title;
	public function setTitle($title) {
		$this->title = $title;
		$this->name = ModelHelper::convertTitleToName($title);
	}

	public function getTitle() {
		return $this->title;
	}

	/**
	 * @var Course
	 */
	public $course;

	public $urls = array();
	public $lessons;

	public $progresses = array();

}
?>
