<?php

/**
 * ContentObject
 */
class ContentObject extends BaseModel {
	public $objectId;
	public $typeId;
	public $content;
	public $title;
	public $description;
}

/**
 * ContentType
 */
class ContentType extends BaseModel {
	public $typeId;
	public $name;
}

/**
 * Content Manager
 */
class ContentManager extends BaseManager {

	/**
	 * creates a ContentObject
	 * @param $ContentObject $contentObject
	 */
	public function createContentObject($contentObject) {
		$this->repository->createContentObject($contentObject);
	}
	/**
	 * creates a ContentType
	 * @param $ContentType $contentType
	 */
	public function createContentType($contentType) {
		$this->repository->createContentType($contentType);
	}
	
	/**
	 * gets ContentType by name
	 * @param string $name
	 * @return ContentType 
	 */	
	public function getContentTypeByName($name) {
		if ($name == "") {
			throw new ParameterException("name is empty");
		}
		$model = $this->repository->getContentTypeByName($name);
		return $model;
	}
	
	/**
	 * gets ContentObjects of a lesson (attachments)
	 * @param int $lessonId
	 * @return array
	 */
	function getLessonAttachments($lessonId) {
		$attachments = $this->repository->getLessonAttachments($lessonId);
		foreach($attachments as $attachment) {
			$this->decodeContent($attachment);
		}
		return $attachments;
	}
	
	/**
	 * decodes content field according to content type
	 * @param ContentObject $attachment
	 */
	private function decodeContent(ContentObject $attachment) {
		$attachment->content = json_decode($attachment->content);
	}

}

/**
 * ContentRepository
 */
class ContentRepository extends BasePdoRepository {

	/**
	 * creates ContentType 
	 * @param ContentType $model
	 */	
	public function createContentType($model) {
		$query = "INSERT INTO content_types (name) VALUES ( ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->name
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
		$query = "SELECT type_id, name FROM content_types where name = ?";
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
		$query = "INSERT INTO content_objects (type_id, content, title, description) VALUES ( ?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->typeId
			, $model->content
			, $model->title
			, $model->description
		);
		$stmt = $this->execute($stmt, $parameters);
		$model->objectId = $this->pdo->lastInsertId();
		return $model;
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
		$query = "SELECT object_id, type_id, content, title, description FROM content_objects where object_id = ?";
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
	 * gets all ContentObjects of a lesson
	 * @param int $lessonId
	 * @return array
	 */	
	public function getLessonAttachments($lessonId) {
		$query = "SELECT o.object_id, type_id, content, title, description FROM content_objects o, attachments a where o.object_id = a.content_id and a.lesson_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($lessonId));

		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = ContentObject::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
}


?>