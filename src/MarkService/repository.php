<?php
/**
 * MarkRepository
 *
 */
class MarkRepository extends BasePdoRepository {
	/**
	 * creates Mark
	 * @param Mark $model
	 */
	public function createMark($model) {
		$query = "INSERT INTO marks (
		user_id
		, entity_type
		, entity_id
		, type
		, created
		) VALUES (
		?, ?, ?, ?, ?
		)";
		$stmt = $this->prepare($query);
		$parameters = array(
				$model->userId
				, $model->entityType
				, $model->entityId
				, $model->type
				, $model->created
		);
		$stmt = $this->execute($stmt, $parameters);
		return $model;
	}

	/**
	 * checks if user marks an entity
	 * @param int $userId
	 * @param EntityTypes $entityType
	 * @param int $entityId
	 * @param MarkTypes $type
	 * @return Mark
	 */
	public function getMark($userId, $entityType, $entityId, $type) {
		$query = "SELECT user_id, entity_type, entity_id, type, created FROM marks where user_id = ? and entity_type = ? and entity_id = ? and type = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($userId, $entityType, $entityId, $type));
		if ($a = $stmt->fetch()) {
			$model = Mark::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	
	/**
	 * deletes mark
	 * @param int $userId
	 * @param EntityTypes $entityType
	 * @param int $entityId
	 * @param MarkTypes $type
	 */
	public function deleteMark($userId, $entityType, $entityId, $type) {
		$query = "DELETE from marks where user_id = ? and entity_type = ? and entity_id = ? and type = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($userId, $entityType, $entityId, $type));
	}
}
