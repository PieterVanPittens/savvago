<?php

/**
 * mark any entity with any type of mark
 * 
 * MarkManager
 */
class MarkManager extends BaseManager {

	/**
	 * MarkRepository
	 * @var MarkRepository
	 */
	protected $repository;
	
	
	/**
	 * marks an entity
	 * @param Mark $mark
	 * @return int increase in number of marks for this entity
	 */
	public function mark(Mark $mark) {
		$exists = $this->repository->getMark($mark->userId, $mark->entityType, $mark->entityId, $mark->type);
		if ($exists == null) {
			// not marked yet, so do it now
			$this->repository->createMark($mark);
			return 1;
		} else {
			// already marked, so unmark now
			$this->repository->deleteMark($mark->userId, $mark->entityType, $mark->entityId, $mark->type);
			return -1;
		}
	}
	
	/**
	 * checks if user has marked an entity
	 * @param int $userId
	 * @param EntityTypes $entityType
	 * @param int $entityId
	 * @param MarkTypes $type
	 */
	public function marksEntity($userId, $entityType, $entityId, $type) {
		$mark = $this->repository->getMark($userId, $entityType, $entityId, $type);
		return $mark != null;
	}
}