<?php

/**
 * JourneyRepository
 *
 */
class JourneyRepository extends BasePdoRepository {

	private $fieldNames = 'name, description, title, user_id, tags, is_active, num_enrollments, num_stations';
	
	/**
	 * creates Journey
	 * @param Journey $model
	 */
	public function createJourney($model) {
		$query = "INSERT INTO journeys (
		name
		, description
		, title
		, user_id
		, tags
		, is_active
		, num_enrollments
		, num_stations
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array(
				$model->name
				, $model->description
				, $model->title
				, $model->userId
				, $model->tags
				, $model->isActive ? 1 : 0
				, $model->numEnrollments
				, $model->numStations
		);
		$stmt = $this->execute($stmt, $parameters);
		$model->journeyId = $this->pdo->lastInsertId();

		return $model;
	}

	/**
	 * gets Journey by name
	 * @param int $journeyId
	 * @return Journey
	 */
	public function getJourneyByName($name) {
		$query = "SELECT journey_id, ".$this->fieldNames." FROM journeys where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));
		if ($a = $stmt->fetch()) {
			$model = Journey::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	

	
	/**
	 * deletes all assignments of lessons to one journey
	 * @param int $journeyId
	 * @param int[] $lessonIds
	 */
	public function deleteJourneyLessons($journeyId, $lessonIds) {
		if (count($lessonIds) > 0) {
			$lessonIdsIn = implode(',', $lessonIds);
			$query = "DELETE from journey_lessons where journey_id = ? AND lesson_id in (".$lessonIdsIn.")";
			$stmt = $this->prepare($query);
			$stmt = $this->execute($stmt, array($journeyId));
		}
	}

	
	/**
	 * deletes all assignments of lessons to one journey
	 * @param int $journeyId
	 */
	public function deleteJourneyLessons2($journeyId) {
		$query = "DELETE from journey_lessons where journey_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($journeyId));
	}
	
	/**
	 * creates assignment journey-lesson
	 * @param int $journeyId
	 * @param int $lessonId
	 */
	public function createJourneyLesson($journeyId, $lessonId) {
		$query = "INSERT INTO journey_lessons (
		journey_id
		, lesson_id
		) VALUES (?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array(
				$journeyId
				, $lessonId
			);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * gets all journeys
	 * @return Journey[]
	 */
	public function getJourneys() {
		$models = array();
		$query = 'select journey_id, '.$this->fieldNames.' from journeys order by title';
		
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		while ($a = $stmt->fetch()) {
			$models[] = Journey::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	

	/**
	 * gets one journey
	 * @param int $journeyId
	 * @return Journey
	 */
	public function getJourney($journeyId) {
		$query = 'select journey_id, '.$this->fieldNames.' from journeys where journey_id = ?';
		
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($journeyId));
		$model = null;
		if ($a = $stmt->fetch()) {
			$model = Journey::CreateModelFromRepositoryArray($a);
		}
		return $model;
	}
	
	/**
	 * deletes a journey
	 * @param int $journeyId
	 */
	public function deleteJourney($journeyId) {
		$query = 'delete from journeys where journey_id = ?';
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($journeyId));
	}
	
	/**
	 * updates journey
	 * @param Journey $journey
	 */
	public function updateJourney(Journey $journey) {
		$query = "UPDATE journeys SET is_active = ? WHERE journey_id = ?";
		
		$stmt = $this->prepare($query);
		$parameters = array(
				$journey->isActive ? 1 : 0
				, $journey->journeyId
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	/**
	 * retrieves top n journeys based on enrollments
	 * @param int $userId
	 * @param int $n
	 * @return Journey[]
	 */
	public function getTopNJourneys($userId, $n) {
		$query = 'SELECT journey_id, '.$this->fieldNames.'
		FROM journeys where is_active = 1
		order by num_enrollments DESC Limit '.$n.'';
	
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Journey::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
}
