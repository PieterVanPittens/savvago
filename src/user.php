<?php

/*
 * strips first line from text
 */
function stripFirstLine($text)
{
	return substr( $text, strpos($text, "\n")+1 );
}

/*
 * gets first line of text
 */
function getFirstLine($text) {
	return strtok($text, "\n"); // subject = first line of email template
}


function getGUID(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
	}else{
		mt_srand((double)microtime()*10000);
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		.substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		.chr(125);// "}"
		return $uuid;
	}
}


/**
 * User Types
 * enum
 */
abstract class UserTypes {
	/**
	 * student that is not registered yet
	 * @var integer
	 */
	const Anonymous = 1;
	/**
	 * Student
	 * @var integer
	 */
	const Student = 2;
	/**
	 * Teacher
	 * @var integer
	 */
	const Teacher = 3;
	/**
	 * Admin
	 * @var integer
	 */
	const Admin = 4;
	
}




class User extends BaseModel implements iModel{
	public $userId;
	public $title;
	public $name;
	public $displayName;
	public $email;
	public $password;
	public $info = '';
	public $urls = array();
	public $verificationKey;
	public $isVerified = false;
	public $isActive = false;
	
	public $passwordRecoveryKey;
	public $passwordRecoveryDeadline;
	
	/**
	 * User Type
	 * @var UserTypes
	 */
	public $type = UserTypes::Anonymous;
	
	public function isGuest() {
		return $this->userId == 0;
	}
	
	public function getId() {
		return $this->userId;
	}
	
	public function isAdmin() {
		return $this->type == UserTypes::Admin;
	}
	public function isTeacher() {
		return $this->type == UserTypes::Teacher || $this->isAdmin();
	}
	public function isStudent() {
		return $this->type == UserTypes::Student || $this->isTeacher() || $this->isAdmin();
	}
	public function isAnonymous() {
		return $this->type == UserTypes::Anonymous || $this->isStudent() || $this->isTeacher() || $this->isAdmin();
	}
}


/**
 * just a container, required for handling of currentuser in slimphp layer
 *
 */
class UserContainer {
	private $user;

	public function getUser() {
		return $this->user;
	}

	public function setUser($user) {
		$this->user = $user;
	}

	public function getGuest() {
		$user = new User();
		$user->userId = 0;
		$user->name = "Guest";
		$user->displayName = "Guest";
		return $user;
	}
}

class UserService extends BaseService {

	/**
	 * gets all users
	 * @return array
	 */
	public function getUsers() {
		$users = $this->container['userManager']->getUsers();
		return $users;
	}
	
	

	/**
	 * promotes/demotes a user
	 * @param unknown $userId
	 * @param unknown $newType
	 */
	public function promoteUser($userId, $newType) {
		$this->container['userManager']->promoteUser($userId, $newType);
	}

	/**
	 * activates/deactivates a user
	 * @param unknown $userId
	 * @param unknown isActive
	 */
	public function activateUser($userId, $isActive) {
		$this->container['userManager']->activateUser($userId, $isActive);
	}
}



/**
 * User Manager
 */
class UserManager extends BaseManager {
	
	/**
	 * activates/deactivates a user
	 * @param unknown $userId
	 * @param unknown isActive
	 */
	public function activateUser($userId, $isActive) {
		$this->container['userRepository']->activateUser($userId, $isActive);
	}
	
	/**
	 * promotes/demotes a user
	 * @param unknown $userId
	 * @param unknown $newType
	 */
	public function promoteUser($userId, $newType) {
		$this->container['userRepository']->promoteUser($userId, $newType);
	}
	
	/**
	 * gets all users
	 * @return array
	 */
	public function getUsers() {
		$users = $this->container['userRepository']->getUsers();
		return $users;
	}
	
	/**
	 * generates urls for a User
	 * @param User $user
	 */
	private function addUserUrls($user) {
		$urls = array(
			'view' => $this->settings['template']['base'] . 'users/'.$user->name
			);
		$user->urls = $urls;		
	}

	/** 
	 * verifies Email Address
	 * @param string $email
	 * @param string $key
	 * @return ApiResult
	 */
	public function verifyEmail($email, $key) {
		$user = $this->repository->getUserByEmail($email);
		if (is_null($user)) {
			throw new NotFoundException("Email needs to exist in our database.");
		}
		
		$result = new ApiResult();
		
		if ($user->verificationKey == $key) {
			$this->repository->verifyUser($user);
			$this->repository->activateUser($user->userId, true);

			$text = "Email verified";
			$result->setSuccess($text);
			/*
			// create email and send		
			$queueItem = new MailQueueItem();
			$queueItem->fromName = "LMS"; // todo
			$queueItem->fromEmail = "LMS@blub.de"; // todo
			$queueItem->toName = $user->displayName;		
			$queueItem->toEmail = $user->email;
			$queueItem->subject = "Email Verified"; // todo
			$queueItem->message = "Congratulations"; // todo
			$this->container['mailQueueManager']->createMailQueueItem($queueItem);
			*/
		} else {
			$text = "The Verification Key needs to match your Email";
			$result->setError($text);
		}
		return $result;
	}
	
	/**
	 * sets new password
	 * @param string $passwordRecoveryKey
	 * @param unknown $password
	 * @return ApiResult
	 */
	public function setNewPassword($passwordRecoveryKey, $password) {
		$user = $this->repository->getUserByPasswordRecoveryKey($passwordRecoveryKey);
		if (is_null($user)) {
			throw new NotFoundException("You should request a new password recovery key");
		}
		if ($password == "") {
			throw new ValidationException("Please provide a new password");			
		}
		if ($user->passwordRecoveryDeadline < time()) {
			throw new ValidationException("You should request a new password recovery key because it has expired.");
		}
		$user->passwordRecoveryKey = null;
		$user->passwordRecoveryDeadline = null;
		$user->password = $password; // Todo encode
		$this->repository->setNewPassword($user);
		
		
		
		$result = new ApiResult();
		$result->setSuccess("Your new password is saved");
		return $result;
	}
	
	/**
	 * gets User by password recovery key
	 * @param string $key
	 * @return User
	 */
	public function getUserByPasswordRecoveryKey($key) {
		return $this->repository->getUserByPasswordRecoveryKey($key);
	}
	
	/**
	 * send password recovery mail
	 * @param string $email
	 * @return ApiResult
	 */
	public function sendPasswordRecoveryLink($email) {
		if ($this->isNullOrEmpty($email)) {
			throw new ValidationException('No email address => no recovery link');
		}
			
		// for security reasons we will always claim that an email has been sent
		$text = "We have sent a recovery link to your email address";
		$result = new ApiResult();
		$result->setSuccess($text);
		
		$user = $this->repository->getUserByEmail($email);
		if (is_null($user)) {
			return $result;
		}
		
		// create token
		$user->passwordRecoveryKey = str_replace('-', '', trim(getGUID(), '{}'));
		// save token in db (valid for 24 hours)
		$user->passwordRecoveryDeadline = time() + (24*3600);
		$this->repository->updatePasswordRecovery($user);
		
		// generate link
		$recoveryLink = $this->settings['application']['base'] . 'newpassword/' . $user->passwordRecoveryKey;
		
		// put link in email template and render template
		$renderer = new Slim\Views\PhpRenderer(__DIR__ . '/../public/templates/email/');
		$response = new Slim\Http\Response();
		$data['recoveryLink'] = $recoveryLink;
		$data['user'] = $user;
		$response = $renderer->render($response, 'forgot.phtml', $data);
		$message = $response->getBody();

		
		$subject = getFirstLine($message);
		$message = stripFirstLine($message);
		
		$isSent = $this->mail($user, $subject, $message);
		

		// send mail
		
		return $result;
	}
	
	/**
	 * registers a new user
	 * @param User $user
	 */
	public function registerUser($user) {
		
		// check input
		$this->checkParameterForNull($user);
		
		if (isset($user->displayName) && $this->isNullOrEmpty($user->displayName)) {
			throw new ValidationException("Display Name is required");
		}
		if (isset($user->email) && $this->isNullOrEmpty($user->email)) {
			throw new ValidationException("Email is required");
		}		
		if (isset($user->password) && $this->isNullOrEmpty($user->password)) {
			throw new ValidationException("Password is required");
		}
		if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
			throw new ValidationException('This is not a valid email address');
		}
		$userExists = $this->repository->getUserByEmail($user->email);
		if (!is_null($userExists)) {
			throw new ValidationException('Email already exists');
		}
		
		
		$verificationKey = str_replace('-', '', trim(getGUID(), '{}'));
		$user->isVerified = 0;
		$user->verificationKey = $verificationKey;
		$user->isActive = 0;
		$user->title = '';
		
		
		// create name in 2 steps:
		// 1. convert to slug
		// 2. make sure slug is unique
		$slug = url_slug($user->displayName);
		$userExists = $this->repository->getUserByName($slug);
		if (is_null($userExists)) {
			$user->name = $slug;
		} else {
			$stop = false;
			$i = 1;
			while (!$stop) {
				$i++;
				$newSlug = $slug.$i;
				$userExists = $this->repository->getUserByName($newSlug);
				if (is_null($userExists)) {
					$user->name = $newSlug;
					$stop = true;
				}
			}
		}
		
		// todo: enable for production
		//$user->password = password_hash($user->password, PASSWORD_DEFAULT);
		$this->repository->createUser($user);
		
		/*
		// create email and send		
		$queueItem = new MailQueueItem();
		$queueItem->fromName = "LMS"; // todo
		$queueItem->fromEmail = "LMS@blub.de"; // todo
		$queueItem->toName = $user->displayName;		
		$queueItem->toEmail = $user->email;
		$queueItem->subject = "Verify Email"; // todo
		*/

		$verificationLink = $verificationKey;
		
		$renderer = new Slim\Views\PhpRenderer(__DIR__ . '/../public/templates/email/');
		$response = new Slim\Http\Response();
		$data['verificationLink'] = $verificationLink;
		$data['user'] = $user;
		$response = $renderer->render($response, 'verify_email.phtml', $data);
		$message = $response->getBody();
		
		$subject = getFirstLine($message);
		$message = stripFirstLine($message);
		
		$isSent = $this->mail($user, $subject, $message);

		/*	
		$queueItem->message = $message;
		$this->container['mailQueueManager']->createMailQueueItem($queueItem);
		*/
		$apiResult = new ApiResult();
		if ($isSent) {
			$apiResult->setSuccess('You are registered now. Please check your emails to verify your account.');
		} else {
			$apiResult->setError('You are registered now. But we could not send an email with your verification key.');
		}
		return $apiResult;
	}

	
	/**
	 * send mail from system account
	 * @param User $toUser
	 * @param string $subject
	 * @param string $message
	 * @return bool is successfully sent
	 */
	function mail($toUser, $subject, $message) {
		$to = $toUser->displayName . ' <'.$toUser->email.'>';
		$from = 'savvago <savvago@domain.com>';
		$header = 'From: ' . $to . '\r\n' .
				'Reply-To: ' . $to . '\r\n' .
				'X-Mailer: PHP/' . phpversion();
		//$isSent = @mail($to, $subject, $message, $header);		
		$isSent = true;
		
		return $isSent;
	}
	
	/**
	 * gets a user by id
	 * @param int $userId
	 * @return User
	 */
	public function getUserById($userId) {
		$user = $this->repository->getUserById($userId);
		if (is_null($user)) {
			return null;
		}
			$user->password = '';
		$this->addUserUrls($user);
		if ($user->info != '') {
			$user->info = json_decode($user->info);
		}
		return $user;
	}

	/**
	 * gets a user by email
	 * @param string $email
	 * @return User
	 */
	public function getUserByEmail($email) {
		$user = $this->repository->getUserByEmail($email);
		if (is_null($user)) {
			return null;
		}
		$user->password = '';
		$this->addUserUrls($user);
		if ($user->info != '') {
			$user->info = json_decode($user->info);
		}
		return $user;
	}
	
	/**
	 * checks a login and returns user
	 * @param string $email
	 * @param string $password
	 * @return User
	 */
	public function getUserByCredentials($email, $password) {
		$user = $this->repository->getUserByEmail($email);
		if (is_null($user)) {
			return null;
		} else {
			if ((true || password_verify($password, $user->password)) && $user->isActive) {
				return $user;
			} else {
				return null;
			}
		}
	}
	
}

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