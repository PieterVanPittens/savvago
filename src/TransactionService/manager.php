<?php

/**
 * TransactionManager
 * only purpose is to manage transactions, used by services
 */
class TransactionManager extends BaseManager {

	/**
	 * TagRepository
	 * @var TagRepository
	 */
	protected $repository;
	
	/**
	 * starts transaction
	 */
	public function start() {
		$this->repository->beginTransaction();
	}
	/**
	 * commits transaction
	 */
	public function commit() {
		$this->repository->commit();
	}
	/**
	 * rolls back transaction
	 */
	public function rollback() {
		$this->repository->rollback();
	}
}