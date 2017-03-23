<?php
/**
 * TagRepository
 *
 */
class TagRepository extends BasePdoRepository {

	/**
	 * get Tag by name
	 * @param string $name
	 * @return Lesson
	 */
	public function getTagByName($name) {
		$query = "SELECT tag_id, name FROM tags where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));
		if ($a = $stmt->fetch()) {
			$model = Tag::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	
	/**
	 * creates Tag
	 * @param Tag $model
	 * @return Tag
	 */
	public function createTag($model) {
		$query = "INSERT INTO tags (
		name
		) VALUES (?)";
		$stmt = $this->prepare($query);
		$parameters = array(
				$model->name
		);
		$stmt = $this->execute($stmt, $parameters);
		$model->tagId = $this->pdo->lastInsertId();
	
		return $model;
	}

	/**
	 * creates Tag-Entity Assignment
	 * @param int $tagId
	 * @param int $entityType
	 * @param int $entityId
	 */
	public function createEntityTag($tagId, $entityType, $entityId) {
		$query = "INSERT INTO entity_tags (
			entity_type, entity_id, tag_id
		) VALUES (?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array(
				$entityType
				, $entityId
				, $tagId
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * checks if entitytag exists
	 * @param int $tagId
	 * @param int $entityType
	 * @param int $entityId
	 */
	public function existsEntityTag($tagId, $entityType, $entityId) {
		$query = "SELECT entity_type, entity_id, tag_id FROM entity_tags where entity_type = ? and entity_id = ? and tag_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array(
			$tagId
			, $entityType
			, $entityId
		));
		if ($a = $stmt->fetch()) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * gets Tags by Names
	 * @param string[] $tagNames
	 * @return Tags[]
	 */
	public function getTagsByNames($tagNames) {
		foreach($tagNames as $tagName) {
			$tagNames2[] = "'".$tagName."'";
		}
		$nameIn = implode(',', $tagNames2);
		
		$query = 'SELECT tag_id, name from tags where name in ('.$nameIn.')';
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Tag::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * deletes
	 * @param EntityTypes $entityType
	 * @param int $journeyId
	 */
	public function deleteEntityTags($entityType, $journeyId) {
		$query = "DELETE from entity_tags where entity_type = ? and entity_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($entityType, $journeyId));
	}
	
}
