<?php
/**
 * Lesson
 *
 */
class Lesson extends BaseModel implements JsonSerializable {
	public $lessonId;
	public $sectionId;
	public $courseId;
	public $name;
	public $contentObjectId;
	public $rank;
	/**
	 * description in markdown
	 */
	public $description;
	/**
	 * description parsed to html
	 */
	public $descriptionHtml;
	public $sectionRank;
	public $imageName;

	public $title;
	public function setTitle($title) {
		$this->title = $title;
		$this->name = ModelHelper::convertTitleToName($title);
	}

	public function getTitle() {
		return $this->title;
	}

	public $progresses = array();
	public $urls = array();


	/**
	 * @var Course
	 */
	public $course;


	/**
	 * @var ContentObject
	 */
	public $content;

	/**
	 * @var Section
	 */
	public $section;


	public function jsonSerialize() {
		return [
				'lessonId' => $this->lessonId,
				'sectionId' => $this->sectionId,
				'courseId' => $this->courseId,
				'name' => $this->name,
				'contentObjectId' => $this->contentObjectId,
				'rank' => $this->rank,
				'description' => $this->description,
				'descriptionHtml' => $this->descriptionHtml,
				'title' => $this->title,
				'content' => $this->content,
				'urls' => $this->urls
		];
	}

}

?>