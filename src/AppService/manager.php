<?php

/**
 * App Manager
 */
class AppManager extends BaseManager {

	function __construct($repository) {
		$this->repository = $repository;
	}

	/**
	 * gets all apps
	 */
	public function getApps() {
		return $this->repository->getApps();
	}

	/**
	 * gets all apps of one role
	 */
	public function getRoleApps($roleId) {
		return $this->repository->getRoleApps($roleId);
	}
	
	
	/**
	 * gets app by name
	 * @param string $name
	 * @return App
	 */
	public function getAppByName($name) {
		$app = $this->repository->getAppByName($name);
		return $app;
	}
}

?>