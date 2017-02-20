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


	private $cacheInfo = array();

	/**
	 * checks if cache for this object is available
	 * @param unknown $objectName
	 * @param unknown $objectId
	 */
	function cacheFind($cacheName, $cacheKey) {
		$cacheTag = $this->createCacheTag($cacheName, $cacheKey);
		$deliverFromCache = false;
		$cacheFileExists = false;
		$tag = $this->repository->getServiceCacheByTag($cacheTag);
		$cacheFileName = $this->settings['cache']['service'] . str_replace(":", "_", $cacheTag);
		if (!is_null($tag)) {
			// valid cache

			// now check if cache file is available on disc
			$cacheFileExists = file_exists($cacheFileName);
			$deliverFromCache = $cacheFileExists;
		}
		$this->cacheInfo[$cacheTag] = array('cacheFileExists' => $cacheFileExists, 'tagExists' => !is_null($tag), 'cacheFileName' => $cacheFileName);
		if ($deliverFromCache) {
			$s = file_get_contents($cacheFileName);
			$a = unserialize($s);
			return $a;
		}
		return null;
	}

	/**
	 * creates the cache tag from cache name and cache key
	 * @param unknown $cacheName
	 * @param unknown $cacheKey
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
	 * @param object $object
	 */
	function updateCache($cacheName, $cacheKey, iCachable $object) {
		$cacheTag = $this->createCacheTag($cacheName, $cacheKey);
		// update cache
		if (!$this->cacheInfo[$cacheTag]['tagExists']) {
			$this->repository->createServiceCache($cacheTag, $object->getModelType(), $object->getId());
		}
		if (!$this->cacheInfo[$cacheTag]['cacheFileExists']) {
			$s = serialize($object);
			$result = file_put_contents($this->cacheInfo[$cacheTag]['cacheFileName'], $s);
			if ($result === false) {
				throw new Exception("Check your configuration file for settings['cache']['service']. It seems the path does not exist or cannot be written to.");
			}
		}
	}



	/**
	 * invalidates all caches for an object
	 * i.e. all cache tags that are associated to this object will be deleted
	 * @param object $object
	 */
	function deleteCaches(iCachable $object) {
		$this->repository->deleteServiceCaches($object->getModelType(), $object->getId());
	}
}

?>