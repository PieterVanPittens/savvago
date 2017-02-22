<?php
/**
 * ServiceCacheRepository
 */
class ServiceCacheRepository extends BasePdoRepository {

	/**
	 * gets cache entry by objecttag
	 * @param string $tag
	 * @return string cache content
	 */
	public function getServiceCacheByTag($tag) {
		$query = "SELECT content FROM service_cache where tag = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($tag));
		if ($a = $stmt->fetch()) {
			return $a['content'];
		} else {
			return null;
		}
	}

	/**
	 * creates service cache entry
	 * @param string $tag
	 * @param ModelTypes $modelType
	 * @param int $modelId
	 * @param string $content
	 */
	public function createServiceCache($tag, $modelType, $modelId, $content) {
		$query = "INSERT INTO service_cache (tag, model_type, model_id, content) VALUES (?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($tag, $modelType, $modelId, $content);
		$stmt = $this->execute($stmt, $parameters);
	}


	/**
	 * updates content of cache
	 * @param int $modelType
	 * @param int $modelId
	 * @param string $content
	 */
	public function updateServiceCache($modelType, $modelId, $content) {
		$query = "UPDATE service_cache SET content = ? where model_type = ? and model_id = ?";
		
		$stmt = $this->prepare($query);
		$parameters = array(
				$content,
				$modelType,
				$modelId
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	
	/**
	 * deletes all service caches associated with an object
	 * @param ModelTypes $modelType
	 * @param int $modelId
	 */
	public function deleteServiceCaches($modelType, $modelId) {
		// content
		$query = "
		delete from service_cache where model_type = ? and model_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
				$modelType, $modelId
		);
		$stmt = $this->execute($stmt, $parameters);
	}


}

?>