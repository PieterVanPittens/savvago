<?php



class Role extends BaseModel implements iEntity {
	public $roleId;
	public $name;
	public $title;
	public $description;

	public function getId() {
		return $this->roleId;
	}
	public function getEntityType() {
		return EntityTypes::Role;
	}
}
