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
}

?>