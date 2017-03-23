<?php

/**
 *  caches results of calls to services
 *  cache management is done in db, cache content is stored on disc
 */
class ServiceCacheManager {

	/**
	 *
	 * @var ServiceCacheRepository
	 */
	private $repository;
	/**
	 * settings array
	 * @var Array
	 */
	private $settings;

	/**
	 * constructor
	 * @param ServiceCacheRepository $repository
	 * @param Array $settings
	 */
	function __construct(
			$repository
			, $settings
			) {
				$this->repository = $repository;
				$this->settings = $settings;
	}

	/**
	 * checks if cache for this object is available
	 * @param string $cacheName name of cache, e.g. course
	 * @param stirng $cacheKey key in cache, e.g. courseId
	 */
	function cacheFind($cacheName, $cacheKey) {
		$cacheTag = $this->createCacheTag($cacheName, $cacheKey);
		$deliverFromCache = false;
		$content = $this->repository->getServiceCacheByTag($cacheTag);
		if (!is_null($content)) {
			// valid cache

			$deliverFromCache = true;
		}
		if ($deliverFromCache) {
			$a = unserialize($content);
			return $a;
		}
		return null;
	}

	/**
	 * creates the cache tag from cache name and cache key
	 * @param string $cacheName
	 * @param string $cacheKey
	 */
	private function createCacheTag($cacheName, $cacheKey) {
		if (is_array($cacheKey)) {
			$cacheTag = $cacheName . ':';
			for ($i = 0; $i < count($cacheKey); $i++) {
				if ($i>0) {
					$cacheTag .= '-';
				}
				$cacheTag .= $cacheKey[$i];
			}
		} else {
			$cacheTag = $cacheName . ':' . $cacheKey;
		}
		return $cacheTag;
	}

	/**
	 * updates cache
	 * @param string $cacheName
	 * @param string $cacheKey
	 * @param iEntity $object
	 */
	function updateCache($cacheName, $cacheKey, iEntity $object) {
		$cacheTag = $this->createCacheTag($cacheName, $cacheKey);
		$s = serialize($object);
		
		$exists = $this->repository->getServiceCacheByTag($cacheTag);
		
		if (is_null($exists)) {
			$this->repository->createServiceCache($cacheTag, $object->getEntityType(), $object->getId(), $s);
		} else {
			$this->repository->updateServiceCache($object->getEntityType(), $object->getId(), $s);
		}
	}

	/**
	 * invalidates all caches for an object
	 * i.e. all cache tags that are associated to this object will be deleted
	 * @param iEntity $object
	 */
	function deleteCaches(iEntity $object) {
		$this->repository->deleteServiceCaches($object->getEntityType(), $object->getId());
	}
}

?>