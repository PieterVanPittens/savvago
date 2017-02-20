<?php
/**
 * ServiceCacheRepository
 */
class ServiceCacheRepository extends BasePdoRepository {

	/**
	 * gets cache entry by objecttag
	 * @param string $tag
	 */
	public function getServiceCacheByTag($tag) {
		$query = "SELECT 1 FROM service_cache where tag = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($tag));
		if ($a = $stmt->fetch()) {
			return '';
		} else {
			return null;
		}
	}

	/**
	 * creates service cache entry
	 * @param string $tag
	 * @param ModelTypes $modelType
	 * @param int $modelId
	 */
	public function createServiceCache($tag, $modelType, $modelId) {
		$query = "INSERT INTO service_cache (tag, model_type, model_id) VALUES (?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($tag, $modelType, $modelId);
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