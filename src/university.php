<?php

/**
 * Model University
 */
class University extends BaseModel implements iModel {
	public $universityId;
	public $name;
	public $title;
	public $description;
	public $urls = array();
	
	public function getId() {
		return $this->universityId;
	}
}

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


class UniversityRepository extends BasePdoRepository {	
	/**
	 * creates University 
	 * @param University $model
	 * @return University 
	 */	
	public function createUniversity($model) {
		$query = "INSERT INTO universities (name, title, description) VALUES (?, ?, ?)";
		$stmt = $this->prepare($query);

		$stmt = $this->execute($stmt, array(
			$model->name
			, $model->title
			, $model->description
			));
		$model->universityId = $this->pdo->lastInsertId();
		return $model;
	}
	
	/**
	 * gets University by name
	 * @param string $name
	 * @return University 
	 */	
	public function getUniversityByName($name) {
		$query = "SELECT university_id, name, title, description FROM universities where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));

		if ($a = $stmt->fetch()) {
			$model = University::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	/**
	 * get University by id
	 * @param int $id
	 * @return University 
	 */	
	public function getUniversityById($id) {
		$dummy = new University();
		$dummy->universityId = $id;
		$university = $this->getFromCacheById($dummy);
		if (is_null($university)) {
			$query = "SELECT university_id, name, title, description FROM universities where university_id = ?";
			$stmt = $this->prepare($query);
			$stmt = $this->execute($stmt, array($id));
			if ($a = $stmt->fetch()) {
				$university = University::CreateModelFromRepositoryArray($a);
				$this->cacheObject($university);
				return $university;
			} else {
				return null;
			}
		} else {
			return $university;
		}
	}
}


?>