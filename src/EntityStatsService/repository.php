<?php
/**
 * EntityStatsRepository
 *
 */
class EntityStatsRepository extends BasePdoRepository {
	/**
	 * creates EntityStat
	 * @param EntityStat $model
	 */
	public function createEntityStat($model) {
		$query = "INSERT INTO entity_stats (
		entity_type
		, entity_id
		, type
		, value
		) VALUES (
		?, ?, ?, ?
		)";
		$stmt = $this->prepare($query);
		$parameters = array(
				$model->entityType
				, $model->entityId
				, $model->type
				, $model->value
		);
		$stmt = $this->execute($stmt, $parameters);

		return $model;
	}
	
	
	/**
	 * increases stat for entity
	 * @param EntityTypes $entityType
	 * @param int $entityId
	 * @param EntityStats $type
	 * @param int $increase
	 * @return int rowsaffected
	 */
	public function increaseEntityStat($entityType, $entityId, $type, $increase) {
		$query = "UPDATE entity_stats SET value = value + ?  WHERE entity_type = ? and entity_id = ? and type = ?";
		
		$stmt = $this->prepare($query);
		$parameters = array(
				$increase, $entityType, $entityId, $type
		);
		$stmt = $this->execute($stmt, $parameters);
		return $stmt->rowCount();
	}

	/**
	 * gets entity stats for one entity
	 * @param EntityTypes $entityType
	 * @param int $entityId
	 * @return EntityStat[]
	 */
	public function getEntityStats($entityType, $entityId) {
		$query = 'select entity_type, entity_id, type, value from entity_stats where entity_type = ? and entity_id = ?';
		
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($entityType, $entityId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = EntityStat::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
}