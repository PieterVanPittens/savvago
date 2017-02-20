<?php


/**
 * App Service
 */
class AppService extends BaseService {


	private $manager;

	function __construct($manager) {
		$this->manager = $manager;
	}

	/**
	 * gets all apps
	 */
	public function getApps() {
		return $this->manager->getApps();
	}
	/**
	 * gets all apps of one role
	 */
	public function getRoleApps($roleId) {
		// todo: security check: does this user have this role?
		return $this->manager->getRoleApps($roleId);
	}
}

?>