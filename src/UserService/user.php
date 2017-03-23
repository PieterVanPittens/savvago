<?php



class User extends BaseModel implements iEntity {
	public $userId;
	public $title;
	public $name;
	public $displayName;
	public $email;
	public $password;
	public $info = '';
	public $urls = array();
	public $verificationKey;
	public $isVerified = false;
	public $isActive = false;

	public $passwordRecoveryKey;
	public $passwordRecoveryDeadline;

	/**
	 * User Type
	 * @var UserTypes
	 */
	public $type = UserTypes::Anonymous;

	public function isGuest() {
		return $this->userId == 0;
	}

	public function getId() {
		return $this->userId;
	}
	public function getEntityType() {
		return EntityTypes::User;
	}
	
	public function isAdmin() {
		return $this->type == UserTypes::Admin;
	}
	public function isTeacher() {
		return $this->type == UserTypes::Teacher || $this->isAdmin();
	}
	public function isStudent() {
		return $this->type == UserTypes::Student || $this->isTeacher() || $this->isAdmin();
	}
	public function isAnonymous() {
		return $this->type == UserTypes::Anonymous || $this->isStudent() || $this->isTeacher() || $this->isAdmin();
	}
}

?>