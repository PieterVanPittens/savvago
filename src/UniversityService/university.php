<?php
/**
 * Model University
 */
class University extends BaseModel implements iModel {
	public $universityId;
	public $name;
	public $title;
	public $description;
	public $urls = array();

	public function getId() {
		return $this->universityId;
	}
}
?>