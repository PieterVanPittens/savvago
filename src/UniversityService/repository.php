<?php

/**
 * UniversityRepository
 *
 */
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