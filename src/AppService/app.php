<?php
/**
 * app model
 */
class App extends BaseModel implements iEntity {
	public $appId;
	public $name;
	public $title;
	public $description;
	public $isActive;
	public $roleId;

	public function getId() {
		return $this->appId;
	}

	public function getEntityType() {
		return EntityTypes::App;
	}
	
}
?>