<?php

class LessonRepository extends BasePdoRepository {

	private $lessonFieldNames = 'name, title, content_object_id, description, is_active, tags, created, user_id';

	/**
	 * retrieves all Lessons
	 * @return Lesson[]
	 */
	public function getLessons() {
		$query = "SELECT lesson_id, " . $this->lessonFieldNames . " FROM lessons ORDER BY name";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Lesson::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * retrieves all Lessons of a Journey
	 * @param int $journeyId
	 * @return Lesson[]
	 */
	public function getJourneyLessons($journeyId) {
		$query = "SELECT lesson_id, " . $this->lessonFieldNames . " FROM lessons where is_active = 1 and lesson_id in (SELECT lesson_id from journey_lessons where journey_id = ?) ORDER BY name";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($journeyId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Lesson::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * deletes a lesson
	 * @param int $lessonId
	 */
	public function deleteLesson($lessonId) {
		$query = "DELETE from lessons where lesson_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($lessonId));
	}


	/**
	 * updates Lesson
	 * @param Lesson $lesson
	 */
	public function updateLesson($lesson) {
		$query = "UPDATE lessons SET description = ?, is_active = ?, tags = ?, title = ? WHERE lesson_id = ?";

		$stmt = $this->prepare($query);
		$parameters = array(
				$lesson->description,
				$lesson->isActive ? 1:0,
				$lesson->tags,
				$lesson->title,
				$lesson->lessonId
		);
		$stmt = $this->execute($stmt, $parameters);
	}



	/**
	 * creates Lesson
	 * @param Lesson $model
	 * @return Lesson
	 */
	public function createLesson($model) {
		$query = "INSERT INTO lessons (
		name
		, title
		, content_object_id
		, user_id
		, is_active
		, tags
		, created
		) VALUES (
		?, ?, ?, ?, ?, ?, ?
		)";
		$stmt = $this->prepare($query);
		$parameters = array(
			$model->name
			, $model->title
			, $model->contentObjectId
			, $model->userId
			, $model->isActive ? 1 : 0
			, $model->tags
			, $model->created
		);
		$stmt = $this->execute($stmt, $parameters);
		$model->lessonId = $this->pdo->lastInsertId();

		return $model;
	}

	/**
	 * get Lesson by id
	 * @param int $lessonId
	 * @return Lesson
	 */
	public function getLessonById($lessonId) {
		$query = "SELECT lesson_id, " . $this->lessonFieldNames . " FROM lessons where lesson_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($lessonId));
		if ($a = $stmt->fetch()) {
			$model = Lesson::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * get Lesson by name
	 * @param int $name
	 * @return Lesson
	 */
	public function getLessonByName($name) {
		$query = "SELECT lesson_id, " . $this->lessonFieldNames . " FROM lessons where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));
		if ($a = $stmt->fetch()) {
			$model = Lesson::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	
	/**
	 * creates Enrollment
	 * @param Enrollment $model
	 */
	public function createEnrollment($model) {
		$query = "INSERT INTO enrollments (user_id, course_id, timestamp) VALUES ( ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->userId
				, $model->courseId
				, $model->timestamp
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * gets Enrollment
	 * @param int $userId
	 * @param int $courseId
	 * @return Enrollment
	 */
	public function getEnrollment($userId, $courseId) {
		$query = "SELECT user_id, course_id, timestamp FROM enrollments where user_id = ? and course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($userId, $courseId));

		if ($a = $stmt->fetch()) {
			$model = Enrollment::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * creates an attachment (-> links content to lesson)
	 * @param int $lessonId
	 * @param int $contentId
	 */
	public function createAttachment($lessonId, $contentId) {
		$query = "INSERT INTO attachments (lesson_id, content_id) VALUES (?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array(
				$lessonId
				, $contentId
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * deletes all assignments of journeys to one lesson
	 * @param int $lessonId
	 */
	public function deleteJourneyLessons2($lessonId) {
		$query = "DELETE from journey_lessons where lesson_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($lessonId));
	}
	
	
}

?>