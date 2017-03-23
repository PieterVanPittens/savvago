<?php


/**
 * App Repository
 */
class AppRepository extends BasePdoRepository {
	private $fieldNames = 'name, title, description, is_active, role_id';

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
		$query = "SELECT app_id, ".$this->fieldNames." FROM apps WHERE role_id = ? ORDER BY role_id desc, name";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($roleId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = App::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * gets app by name
	 * @param string $name
	 * @return App
	 */
	public function getAppByName($name) {
		$query = "SELECT app_id, ".$this->fieldNames." FROM apps where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));
		if ($a = $stmt->fetch()) {
			return App::CreateModelFromRepositoryArray($a);
		}
		return null;
		
	}
	
}

?>