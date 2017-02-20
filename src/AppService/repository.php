<?php


/**
 * App Repository
 */
class AppRepository extends BasePdoRepository {
	private $fieldNames = 'name, title, description, is_active';

	/**
	 * gets all apps
	 */
	public function getApps() {
		$query = "SELECT app_id, ".$this->fieldNames." FROM apps ORDER BY name";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = App::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * gets all apps of one role
	 */
	public function getRoleApps($roleId) {
		$query = "SELECT a.app_id, ".$this->fieldNames." FROM apps a, role_apps ra WHERE a.app_id = ra.app_id and ra.role_id = ? ORDER BY name";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($roleId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = App::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

}

?>