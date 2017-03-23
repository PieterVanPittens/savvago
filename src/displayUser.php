<?php
/**
 * simple data transfer object
 */
class DisplayUser extends BaseModel implements iEntity {
	public $userId;
	public $title;
	public $name;
	public $displayName;
	public $info = '';
	public $urls = array();
	
	/**
	 * User Type
	 * @var UserTypes
	 */
	public $type = UserTypes::Anonymous;
	
	public function getId() {
		return $this->userId;
	}
	public function getEntityType() {
		return EntityTypes::User;
	}
	}


/** 
 * DisplayUser Repository
 */
class DisplayUserRepository extends BasePdoRepository {
	
	/**
	 * get DisplayUser by id
	 * @param int $id
	 * @return DisplayUser 
	 */	
	public function getUserById($id) {
		$dummy = new DisplayUser();
		$dummy->userId = $id;
		$user = $this->getFromCacheById($dummy);
		if (is_null($user)) {
			$query = "SELECT user_id, title, name, display_name, info FROM users where user_id = ?";
			$stmt = $this->prepare($query);
			$stmt = $this->execute($stmt, array($id));
			if ($a = $stmt->fetch()) {
				$user = DisplayUser::CreateModelFromRepositoryArray($a);
				$this->cacheObject($user);
				return $user;
			} else {
				return null;
			}
		} else {
			return $user;
		}
	}
}

?>