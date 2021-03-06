<?php

/**
 * PDO Repository Implementation
 *
 */
class BasePdoRepository {

	/**
	 * object caches
	 */
	protected $objectCaches = array();

	public function cacheObject(iEntity $object) {
		if (is_null($object)) {
			throw new RepositoryException('object must not be null');
		}
		//  cache exists?
		$cacheName = get_class($object);
		if (isset($this->objectCaches[$cacheName])) {
			$cache = $this->objectCaches[$cacheName];
		} else {
			$cache = array();
			$this->objectCaches[$cacheName] = $cache;
		}
		// object already cached?
		$filtered = array_filter(
				$cache,
				function ($e) use($object) {
					return $e->getId() == $object->getId();
				}
				);
		// no, so let's cache
		if (count($filtered) == 0) {
			$this->objectCaches[$cacheName][] = $object;
		}
	}

	/**
	 * gets object from cache by id
	 * only objectid needs to be set
	 * @param iEntity $object
	 * @return iEntity
	 */
	public function getFromCacheById(iEntity $object) {
		if (is_null($object)) {
			throw new RepositoryException('object must not be null');
		}
		//  cache exists?
		$cacheName = get_class($object);
		if (!isset($this->objectCaches[$cacheName])) {
			return null;
		}
		$cache = $this->objectCaches[$cacheName];
		// object cached?
		$filtered = array_filter(
				$cache,
				function ($e) use($object) {
					return $e->getId() == $object->getId();
				}
				);
		if (count($filtered) == 1) {
			return array_pop($filtered);
		} else {
			return null;
		}
	}

	/**
	 * PDO instance
	 * @var PDO
	 */
	public $pdo;
	

	function __construct($pdo) {
		$this->pdo = $pdo;
	}


	protected function prepare($query) {
		try {
			$stmt = $this->pdo->prepare($query);
		} catch (PDOException $e) {
			throw new RepositoryException($stmt->queryString, $e->getMessage());
		}
		return $stmt;
	}

	protected function execute($statement, $parameters) {
		try {
			if (count($parameters) > 0 ) {
				$statement->execute($parameters);
			} else {
				$statement->execute();
			}
			return $statement;
		} catch (PDOException $e) {
			var_dump($e->getMessage());
			var_dump($statement->queryString);
			var_dump($parameters);
			die();
			throw new RepositoryException($e->getMessage(), 0, $e );
		}
	}

	public function beginTransaction() {
		$this->pdo->beginTransaction();
	}
	public function commit() {
		$this->pdo->commit();
	}
	public function rollback() {
		$this->pdo->rollback();
	}

}
?>