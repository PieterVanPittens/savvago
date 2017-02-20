<?php

/**
 * UniversityService
 *
 */
class UniversityService extends BaseService {
	/**
	 * gets University by name
	 * @param string $name
	 * @return University
	 */
	public function getUniversityByName($name) {
		$cacheHit = $this->cacheFind("university", $name);
		if (!is_null($cacheHit)) {
			return $cacheHit;
		}

		$university = $this->container['universityManager']->getUniversityByName($name);
		$this->cacheUpdate("university", $name, $university);
		return $university;

	}
}
?>