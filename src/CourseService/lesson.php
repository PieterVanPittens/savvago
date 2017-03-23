<?php
/**
 * Lesson
 *
 */
class Lesson extends BaseModel implements iEntity, JsonSerializable {
	public $lessonId;
	
	
	public $userId;

	/**
	 * created by user
	 * @var User
	 */
	public $user;
	
	public $name;
	public $contentObjectId;
	/**
	 * description in markdown
	 */
	public $description;
	/**
	 * description parsed to html
	 */
	public $descriptionHtml;
	
	/**
	 * all stats for this lessons, e.g. numViews, numLikes, ...
	 * @var Array
	 */
	public $stats;
	
	/**
	 * created date time
	 * @var int
	 */
	public $created;
	public $isActive;
	public $tags;
	
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
	 * @var ContentObject
	 */
	public $content;

	public function jsonSerialize() {
		return [
				'lessonId' => $this->lessonId,
				'name' => $this->name,
				'contentObjectId' => $this->contentObjectId,
				'description' => $this->description,
				'descriptionHtml' => $this->descriptionHtml,
				'title' => $this->title,
				'content' => $this->content,
				'isActive' => $this->isActive,
				'tags' => $this->tags,
				'created' => $this->created,
				'numViews' => $this->numViews,
				'numLikes' => $this->numLikes,
				'numFinished' => $this->numFinished,
				'urls' => $this->urls,
				'user' => json_encode($this->user),
				'stats' => $this->stats
		];
	}
	
	public function getId() {
		return $this->lessonId;
	}
	
	public function getEntityType() {
		return EntityTypes::Lesson;
	}
}

?>