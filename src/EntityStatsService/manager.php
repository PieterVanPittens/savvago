<?php

/**
 * EntityStatsManager
 */
class EntityStatsManager extends BaseManager {

	/**
	 * EntityStatsRepository
	 * @var EntityStatsRepository
	 */
	protected $repository;

	/**
	 * increase EntityStat
	 * @param EntityTypes $entityType
	 * @param int $entityId
	 * @param EntityStats $type
	 * @param int $increase
	 * @return ApiResult
	 */
	public function increaseEntityStat($entityType, $entityId, $type, $increase) {
		$rowCount = $this->repository->increaseEntityStat($entityType, $entityId, $type, $increase);
		if ($rowCount == 0) {
			$entityStat = new EntityStat();
			$entityStat->entityType = $entityType;
			$entityStat->entityId = $entityId;
			$entityStat->type = $type;
			$entityStat->value = $increase;
			$this->repository->createEntityStat($entityStat);
		}
		
		$apiResult = ApiResultFactory::CreateSuccess("Stat created", null);
		return $apiResult;
	}
	
	/**
	 * gets all stats for an entity
	 * @param unknown $entityType
	 * @param unknown $entityId
	 * @return Array[]
	 */
	public function getEntityStats($entityType, $entityId) {
		$stats = $this->repository->getEntityStats($entityType, $entityId);
		$s = array();
		// fill in all stats from db
		foreach($stats as $stat) {
			$s[$stat->type] = $stat->value;
		}
		// fill in all other stats that do not exist yet
		if (!isset($s[EntityStats::numLikes])) {
			$s[EntityStats::numLikes] = 0;
		}
		if (!isset($s[EntityStats::numStationChecks])) {
			$s[EntityStats::numStationChecks] = 0;
		}
		if (!isset($s[EntityStats::numViews])) {
			$s[EntityStats::numViews] = 0;
		}
		if (!isset($s[EntityStats::numComments])) {
			$s[EntityStats::numComments] = 0;
		}
		return $s;
	}
}
