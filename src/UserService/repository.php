<?php

/**
 * User Repository
 */
class UserRepository extends BasePdoRepository {

	private $fieldNames = 'name, title, display_name, email, password, info, is_verified, verification_key, type, is_active, password_recovery_key, password_recovery_deadline';


	/**
	 * activates/deactivates a user
	 * @param unknown $userId
	 * @param unknown isActive
	 */
	public function activateUser($userId, $isActive) {
		$query = "UPDATE users SET is_active = ? WHERE user_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
				$isActive,
				$userId
		);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * promotes/demotes a user
	 * @param unknown $userId
	 * @param unknown $newType
	 */
	public function promoteUser($userId, $newType) {
		$query = "UPDATE users SET type = ? WHERE user_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
				$newType,
				$userId
		);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * creates User
	 * @param User $model
	 * @return User
	 */
	public function createUser($model) {
		$query = "INSERT INTO users (".$this->fieldNames . ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->name
				, $model->title
				, $model->displayName
				, $model->email
				, $model->password
				, $model->info
				, $model->isVerified
				, $model->verificationKey
				, $model->type
				, $model->isActive
				, null
				, null
		);
		$stmt = $this->execute($stmt, $parameters);
		$model->userId = $this->pdo->lastInsertId();
		return $model;
	}

	/**
	 * get User by id
	 * @param int $id
	 * @return User
	 */
	public function getUserById($id) {
		$dummy = new User();
		$dummy->userId = $id;
		$user = $this->getFromCacheById($dummy);
		if (is_null($user)) {
			$query = "SELECT user_id, ".$this->fieldNames." FROM users where user_id = ?";
			$stmt = $this->prepare($query);
			$stmt = $this->execute($stmt, array($id));
			if ($a = $stmt->fetch()) {
				$user = User::CreateModelFromRepositoryArray($a);
				$this->cacheObject($user);
				return $user;
			} else {
				return null;
			}
		} else {
			return $user;
		}
	}

	/**
	 * gets User by email
	 * @param string $email
	 * @return User
	 */
	public function getUserByEmail($email) {
		$query = "SELECT user_id, ".$this->fieldNames." FROM users where email = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($email));
		if ($a = $stmt->fetch()) {
			$model = User::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * gets User by password recovery key
	 * @param string $key
	 * @return User
	 */
	public function getUserByPasswordRecoveryKey($key) {
		$query = "SELECT user_id, ".$this->fieldNames." FROM users where password_recovery_key = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($key));
		if ($a = $stmt->fetch()) {
			$model = User::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * gets User by name
	 * @param string $name
	 * @return User
	 */
	public function getUserByName($name) {
		$query = "SELECT user_id, ".$this->fieldNames." FROM users where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));
		if ($a = $stmt->fetch()) {
			$model = User::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}


	/**
	 * sets User to verified
	 * @param User $user
	 */
	public function verifyUser($user) {
		$query = "UPDATE users SET is_verified = 1, verification_key = null WHERE user_id = ?";

		$stmt = $this->prepare($query);
		$parameters = array(
				$user->userId
		);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * sets passwordrecoverykey and deadline
	 * @param User $user
	 */
	public function updatePasswordRecovery(User $user) {
		$query = "UPDATE users SET password_recovery_key = ?, password_recovery_deadline = ? WHERE user_id = ?";

		$stmt = $this->prepare($query);
		$parameters = array(
				$user->passwordRecoveryKey,
				$user->passwordRecoveryDeadline,
				$user->userId
		);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * sets new password
	 * @param User $user
	 */
	public function setNewPassword(User $user) {
		$query = "UPDATE users SET password_recovery_key = ?, password_recovery_deadline = ?, password = ? WHERE user_id = ?";

		$stmt = $this->prepare($query);
		$parameters = array(
				$user->passwordRecoveryKey,
				$user->passwordRecoveryDeadline,
				$user->password,
				$user->userId
		);
		$stmt = $this->execute($stmt, $parameters);
	}


	public function getUsers() {
		$query = "SELECT user_id, name, title, display_name, email, info, is_verified, type, is_active from users order by email";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = User::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

}
?>
