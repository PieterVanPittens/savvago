<?php

/**
 * University Manager
 */
class UniversityManager extends BaseManager {

	/**
	 * gets University by name
	 * @param string $name
	 * @return University
	 */
	public function getUniversityByName($name) {
		if ($name == "") {
			throw new ParameterException("name is empty");
		}
		$model = $this->repository->getUniversityByName($name);
		if ($model == null) {
			throw new NotFoundException($name);
		}
		$this->addUniversityUrls($model);
		return $model;
	}


	/**
	 * gets University by id
	 * @param int $universityId
	 * @return University
	 */
	public function getUniversityById($universityId) {
		$model = $this->repository->getUniversityById($universityId);
		$this->addUniversityUrls($model);
		return $model;
	}


	/**
	 * generate all urls for a University
	 * @param University $university
	 */
	private function addUniversityUrls($university) {
		$urls['view'] = $this->settings['template']['base'] . 'university/'. $university->name;
		$university->urls = $urls;
	}

	/**
	 * creates a new university
	 * @param University $university
	 * @return University
	 */
	public function createUniversity($university) {
		return $this->repository->createUniversity($university);
	}
}


?>