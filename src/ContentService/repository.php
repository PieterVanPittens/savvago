<?php

/**
 * ContentRepository
 */
class ContentRepository extends BasePdoRepository {

	/**
	 * creates ContentType
	 * @param ContentType $model
	 */
	public function createContentType($model) {
		$query = "INSERT INTO content_types (name, is_internal, extension) VALUES ( ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->name
				, $model->isInternal
				, $model->extension
		);
		$stmt = $this->execute($stmt, $parameters);
		$model->name = $this->pdo->lastInsertId();
	}

	/**
	 * gets ContentType by name
	 * @param string $name
	 * @return ContentType
	 */
	public function getContentTypeByName($name) {
		$query = "SELECT type_id, name, is_internal, extension FROM content_types where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));

		if ($a = $stmt->fetch()) {
			$model = ContentType::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * gets ContentType by extension
	 * @param string $name
	 * @return ContentType
	 */
	public function getContentTypeByExtension($name) {
		$query = "SELECT type_id, name, is_internal, extension FROM content_types where extension = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));

		if ($a = $stmt->fetch()) {
			$model = ContentType::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * creates ContentObject
	 * @param ContentObject $model
	 * @return ContentObject
	 */
	public function createContentObject($model) {
		$query = "INSERT INTO content_objects (type_id, course_id, content, name, description, md5_hash) VALUES (?, ?, ?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->typeId
				, $model->courseId
				, $model->content
				, $model->name
				, $model->description
				, $model->md5Hash
		);
		$stmt = $this->execute($stmt, $parameters);
		$model->objectId = $this->pdo->lastInsertId();
		return $model;
	}

	/**
	 * updates md5hash of ContentObject
	 * @param ContentObject $model
	 */
	public function updateMd5Hash($model) {
		$query = "UPDATE content_objects SET md5_hash = ? where object_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
				$model->md5Hash
				, $model->objectId
		);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * gets ContentObject by id
	 * @param string $id
	 * @return ContentObject
	 */
	public function getContentObjectById($id) {
		if (is_null($id)) {
			throw new RepositoryException('id must not be null');
		}
		$query = "SELECT object_id, course_id, type_id, content, name, description, md5_hash FROM content_objects where object_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($id));

		if ($a = $stmt->fetch()) {
			$model = ContentObject::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * gets ContentObject by name
	 * @param int $courseId
	 * @param string $name
	 * @return ContentObject
	 */
	public function getContentObjectByName($courseId, $name) {
		$query = "SELECT object_id, course_id, type_id, content, name, description, md5_hash FROM content_objects where course_id = ? and name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId, $name));

		if ($a = $stmt->fetch()) {
			$model = ContentObject::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}


	/**
	 * gets all ContentObjects of a lesson
	 * @param int $lessonId
	 * @return array
	 */
	public function getLessonAttachments($lessonId) {
		$query = "SELECT o.object_id, o.course_id, type_id, content, name, description, md5_hash FROM content_objects o, attachments a where o.object_id = a.content_id and a.lesson_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($lessonId));

		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = ContentObject::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * gets ContentObjects of a course
	 * @param int $courseId
	 * @return array
	 */
	function getCourseContents($courseId) {
		$query = "SELECT o.object_id, o.course_id, o.type_id, o.content, o.name as oname, o.description, o.md5_hash, t.name as tname, t.is_internal, t.extension
		FROM content_objects o, content_types t where o.type_id = t.type_id and o.course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));

		$models = array();
		while ($a = $stmt->fetch()) {
			$contentObject = ContentObject::CreateModelFromRepositoryArray($a);
			$contentObject->name = $a["oname"];
			$contentType = ContentType::CreateModelFromRepositoryArray($a);
			$contentType->name = $a["tname"];
			$contentObject->type = $contentType;
			$models[] = $contentObject;
		}
		return $models;
	}
}

?>