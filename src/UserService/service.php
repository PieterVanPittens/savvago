<?php
/**
 * UserService
 *
 */
class UserService extends BaseService {

	/**
	 * ContextUser
	 * @var User
	 */
	private $contextUser;

	/**
	 * @var UserManager
	 */
	private $manager;

	/**
	 * @var ServiceCacheManager
	 */
	private $serviceCacheManager;

	/**
	 * constructor
	 * @param User $contextUser
	 * @param CourseManager $manager
	 * @param ServiceCacheManager $serviceCacheManager
	 */
	function __construct($contextUser, $manager, $serviceCacheManager) {
		$this->contextUser = $contextUser;
		$this->manager = $manager;
		$this->serviceCacheManager = $serviceCacheManager;
	}

	/**
	 * gets all users
	 * @return array
	 */
	public function getUsers() {
		$users = $this->manager->getUsers();
		return $users;
	}

	/**
	 * promotes/demotes a user
	 * @param unknown $userId
	 * @param unknown $newType
	 */
	public function promoteUser($userId, $newType) {
		$this->manager->promoteUser($userId, $newType);
	}

	/**
	 * activates/deactivates a user
	 * @param unknown $userId
	 * @param unknown isActive
	 */
	public function activateUser($userId, $isActive) {
		$this->manager->activateUser($userId, $isActive);
	}
}

?>