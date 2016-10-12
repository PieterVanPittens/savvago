<?php

function notAuthenticated($app, $response) {
	$app->response = $response->withStatus(401);
	$apiResult = new ApiResult();
	$apiResult->message = 'Please login my friend...';
	$apiResult->type = MessageTypes::Error;
	return json_encode($apiResult);
}


/**
 * gets input from request body as object
 * json converted to object + all content htmlencoded for security reasons
 * 
 */
function getRequestObject() {
	$input = json_decode(file_get_contents('php://input'));
	if (is_null($input)) {
		throw new ValidationException("Request is empty, object expected");
	}
	foreach($input as $key => $value) {
		$input->$key = htmlentities($value);
	}
	return $input;	
}

// registers new user
$app->post('/api/users', function ($request, $response, $args) {
	$userManager = $this->serviceContainer['userManager'];

	$input = getRequestObject();
	$user = new User();
	$user->displayName = $input->displayName;
	$user->email = $input->email;
	$user->password = $input->password;
	
	$apiResult = $userManager->registerUser($user);
	$apiResult->object = createJwtToken($user->userId);	

	return json_encode($apiResult);
});

// gets list of all users
$app->get('/api/users', function ($request, $response, $args) {
	if (!isAuthenticated($this)) {
		return notAuthenticated($this, $response);
	}
	// todo security check: ist user ein admin?

	$userService = $this->serviceContainer['userService'];
	$users = $userService->getUsers();

	return json_encode(array('data' => $users));
});


// promotes/demotes a user
$app->post('/api/users/{userId}/promote', function ($request, $response, $args) {
	if (!isAuthenticated($this)) {
		return notAuthenticated($this, $response);
	}
	// todo security check: ist user ein admin?

	
	$input = getRequestObject();

	$userId = $args['userId'];
	$newType = $input->type;
	
	$userService = $this->serviceContainer['userService'];
	$userService->promoteUser($userId, $newType);

	$apiResult = new ApiResult();
	$apiResult->message = "User Type changed";
	return json_encode($apiResult);
	
});

// activates a user
$app->post('/api/users/{userId}/activate', function ($request, $response, $args) {
	if (!isAuthenticated($this)) {
		return notAuthenticated($this, $response);
	}
	// todo security check: ist user ein admin?


	$input = getRequestObject();

	$userId = $args['userId'];
	$isActive = $input->active == "true";

	$userService = $this->serviceContainer['userService'];
	$userService->activateUser($userId, $isActive);

	$apiResult = new ApiResult();
	$apiResult->message = "User Status changed";
	return json_encode($apiResult);

});

// create curriculum based on string
$app->get('/api/courses/{courseId}/lessons', function ($request, $response, $args) {
	//if (!isAuthenticated($this)) {
	//	return notAuthenticated($this, $response);
	//}
	// todo security check: ist user der autor dieses kurses?

	$courseService = $this->serviceContainer['courseService'];
	$courseManager = $this->serviceContainer['courseManager'];
	$course = $courseManager->getCourseById($args['courseId'], false);

	$lessons = $courseService->getCourseLessons($course);

	return json_encode(array('data' => $lessons));

});

// create curriculum based on string
$app->post('/api/courses/{courseId}/reorderlesson', function ($request, $response, $args) {
	//if (!isAuthenticated($this)) {
	//	return notAuthenticated($this, $response);
	//}
	// todo security check: ist user der autor dieses kurses?

	$input = getRequestObject();

	
	$courseManager = $this->serviceContainer['courseManager'];
	$course = $courseManager->getCourseById($args['courseId'], false);

	$courseManager->reorderLesson($course, $input->sourceId, $input->targetId);
	
	
	$apiResult = new ApiResult();
	$apiResult->message = "lessons reordered";
	return json_encode($apiResult);

});




// create curriculum based on string
$app->post('/api/courses/{courseId}/curriculum', function ($request, $response, $args) {
	if (!isAuthenticated($this)) {
		return notAuthenticated($this, $response);
	}
	// todo security check: ist user der autor dieses kurses?

	$input = getRequestObject();

	$curriculum = $input->quickEdit;
	$courseManager = $this->serviceContainer['courseManager'];
	$course = $courseManager->getCourseById($args['courseId'], false);
	$courseManager->quickCreateCurriculum($course, $curriculum);
	
	$apiResult = new ApiResult();
	$apiResult->message = "curriculum created";
	$apiResult->object = $course;
	return json_encode($apiResult);
	
});

// create course
$app->post('/api/courses', function ($request, $response, $args) {
	if (!isAuthenticated($this)) {
		return notAuthenticated($this, $response);
	}

	$courseService = $this->serviceContainer['courseService'];
	$courseJson = getRequestObject();

	$course = new Course();
	$course->setTitle($courseJson->title);
	$course->universityId = 1; //$this->userService->getUser()->universityId;
	$courseService->createCourse($course);
	
	$apiResult = new ApiResult();
	$apiResult->message = 'A new course was born';
	$apiResult->object = $course;
	return json_encode($apiResult);
});


/**
 * creates jwt token for user
 */
function createJwtToken($userId) {
	$key = "wer34rwerwrqw23";
	$token = array(
			'userId' => $userId,
			'iss' => 'savvago',
			'iat' => time(),
			'exp' => time()+3600
	);
	
	$jwt = \Firebase\JWT\JWT::encode($token, $key);
	return $jwt;
}


// login
$app->post('/api/login', function ($request, $response, $args) {
	$credentials = getRequestObject();
	if (!isset($credentials->email) || !isset($credentials->password)) {
		$apiResult = new ApiResult();
		$apiResult->setError("The email or password you entered are incorrect");
		return $apiResult->toJson();
	}
	$user = $this->serviceContainer['userManager']->getUserByCredentials($credentials->email, $credentials->password);
	if (is_null($user)) {
		$apiResult = new ApiResult();
		$apiResult->setError("The email or password you entered are incorrect");
		return $apiResult->toJson();
	} else {
		$jwt = createJwtToken($user->userId);
		
		$apiResult = new ApiResult();
		$apiResult->setSuccess("Logged in");
		$apiResult->object = $jwt;
		return $apiResult->toJson();
	}
});

// forgot password, send recovery link
$app->post('/api/forgot', function ($request, $response, $args) {
	$credentials = getRequestObject();
	$email = "";
	if (isset($credentials->email)) {
		$email = $credentials->email;
	}
	$result = $this->serviceContainer['userManager']->sendPasswordRecoveryLink($email);
	return $result->toJson();
});
	
// verify email
$app->post('/api/verify', function ($request, $response, $args) {
	$credentials = getRequestObject();
	$email = "";
	$key = "";
	if (isset($credentials->email)) {
		$email = $credentials->email;
	}
	if (isset($credentials->key)) {
		$key = $credentials->key;
	}
	
	$result = $this->serviceContainer['userManager']->verifyEmail($email, $key);
	
	return $result->toJson();
});

// set newpassword
$app->post('/api/newpassword', function ($request, $response, $args) {
	$credentials = getRequestObject();
	$password = "";
	$key = "";
	if (isset($credentials->password)) {
		$password = $credentials->password;
	}
	if (isset($credentials->key)) {
		$key = $credentials->key;
	}

	$result = $this->serviceContainer['userManager']->setNewPassword($key, $password);

	return $result->toJson();
});

// finish lesson
$app->post('/api/lessons/{lessonId}/finish', function ($request, $response, $args) {
	$courseManager = $this->serviceContainer['courseManager'];
	$courseService = $this->serviceContainer['courseService'];
	$lesson = $courseManager->getLessonById($args['lessonId'], false);
	$result = $courseService->finishLesson($lesson);
	return $result->toJson();	
});
	