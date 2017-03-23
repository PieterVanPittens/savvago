<?php
/**
 * Journey
 */
class Journey extends BaseModel implements iEntity {
	public $journeyId;
	public $name;

	public $userId;
	
	public $isActive;
	
	/**
	 * string of all tags
	 * @var string
	 */
	public $tags;
	
	public $title;
	
	public $numEnrollments;
	public $numStations;
	
	public $urls = array();
	
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
