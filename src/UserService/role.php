<?php



class Role extends BaseModel implements iModel {
	public $roleId;
	public $name;
	public $title;
	public $description;

	public function getId() {
		return $this->roleId;
	}
}

?>