<?php
/**
 * app model
 */
class App extends BaseModel implements iModel {
	public $appId;
	public $name;
	public $title;
	public $description;
	public $isActive;


	public function getId() {
		return $this->appId;
	}
}
?>