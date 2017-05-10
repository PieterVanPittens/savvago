<?php
/**
 * Journey
 */
class Journey extends BaseModel implements iEntity {
	public $journeyId;
	public $name;

	public $userId;
	public $user;
	
	public $isActive;
	
	/**
	 * string of all tags
	 * @var string
	 */
	public $tags;
	
	public $title;
	
	public $numEnrollments;
	public $numStations;
	
	public $description;
	public $descriptionHtml;
	
	public $urls = array();
	
	public $stats;
	
	public function setTitle($title) {
		$this->title = $title;
		$this->name = ModelHelper::convertTitleToName($title);
	}

	public function getTitle() {
		return $this->title;
	}

	public function getId() {
		return $this->journeyId;
	}

	public function getEntityType() {
		return EntityTypes::Journey;
	}
}
