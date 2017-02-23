<?php

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

		$machineSalt = $this->settings['security']['salt'];
		$user->password = password_hash($machineSalt.$user->password, PASSWORD_DEFAULT);

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
		$renderer = new Slim\Views\PhpRenderer($this->settings['renderer']['template_path']);
		$response = new Slim\Http\Response();
		$data['recoveryLink'] = $recoveryLink;
		$data['user'] = $user;
		$response = $renderer->render($response, 'email/forgot.phtml', $data);
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

		$machineSalt = $this->settings['security']['salt'];
		$user->password = password_hash($machineSalt.$user->password, PASSWORD_DEFAULT);
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

		$verificationLink = $this->settings['application']['base'].'users/'.urlencode($user->email) .'/verify/'.urlencode($verificationKey);

		$renderer = new Slim\Views\PhpRenderer($this->settings['renderer']['template_path']);
		$response = new Slim\Http\Response();
		$data['verificationLink'] = $verificationLink;
		$data['user'] = $user;
		$response = $renderer->render($response, 'email/verify_email.phtml', $data);
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
		$from = $this->settings['application']['senderEmail'];
		$header = 'From: ' . $from . '\r\n' .
				'Reply-To: ' . $from . '\r\n';
		// todo activate
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
			$machineSalt = $this->settings['security']['salt'];
			$saltedPassword = $machineSalt.$password;

			if ((password_verify($saltedPassword, $user->password)) && $user->isActive) {
				return $user;
			} else {
				return null;
			}
		}
	}
}
?>
