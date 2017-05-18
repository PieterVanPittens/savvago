<?php

/**
 * gets input from request body as object
 * json converted to object + all content htmlencoded for security reasons
 * 
 */
function getRequestObject() {
	$content = file_get_contents('php://input');
	$input = json_decode($content);
	if (is_null($input)) {
		$apiResult = ApiResultFactory::createError("body does not contain valid json", null);
		http_response_code(400);
		echo $apiResult->toJson();
		die();
	}
	foreach($input as $key => $value) {
		if (!is_array($value) && !is_object($value)) {
			$input->$key = htmlentities($value);
		}
	}
	return $input;	
}

// registers new user
$app->post('/api/users', function ($request, $response, $args) {
	
	$input = getRequestObject();
	
	$userManager = $this->serviceContainer['userManager'];

	$user = new User();
	$user->displayName = $input->displayName;
	$user->email = $input->email;
	$user->password = $input->password;
	try {
	$apiResult = $userManager->registerUser($user);
	} catch (Exception $e) {
		return $e->getMessage();
	}
	$key = $this->serviceContainer['settings']['security']['tokenKey'];
	$apiResult->object = array(
			"token" => createJwtToken($user->userId, $key),	// login user automatically
			"user" => $user
			);

	return $apiResult->toJson();
});

// gets list of all users
$app->get('/api/users', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user ein admin?

	$userService = $this->serviceContainer['userService'];
	$users = $userService->getUsers();

	return json_encode(array('data' => $users));
});


// promotes/demotes a user
$app->post('/api/users/{userId}/promote', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user ein admin?

	
	$input = getRequestObject();

	$userId = $args['userId'];
	$newType = $input->type;
	
	$userService = $this->serviceContainer['userService'];
	$userService->promoteUser($userId, $newType);

	$apiResult = ApiResultFactory::CreateSuccess("User Type changed", null);
	return json_encode($apiResult);
	
});

// activates a user
$app->post('/api/users/{userId}/activate', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user ein admin?


	$input = getRequestObject();

	$userId = $args['userId'];
	$isActive = $input->isActive == "true" || $input->isActive == 1 ? 1 : 0;

	$userService = $this->serviceContainer['userService'];
	$userService->activateUser($userId, $isActive);

	$apiResult = ApiResultFactory::CreateSuccess("User Status changed", null);
	return json_encode($apiResult);

});




// reorder lesson
$app->post('/api/courses/{courseId}/reorderlesson', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user der autor dieses kurses?
	$input = getRequestObject();
	
	$courseManager = $this->serviceContainer['courseManager'];
	$course = $courseManager->getCourseById($args['courseId'], false);

	$courseManager->reorderLesson($course, $input->sourceId, $input->targetId);
	
	$apiResult = new ApiResult();
	$apiResult->message = "lessons reordered";
	return json_encode($apiResult);

});

// reorder section
$app->post('/api/sections/switch', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user der autor dieses kurses?
	$input = getRequestObject();

	$courseManager = $this->serviceContainer['courseManager'];
	$apiResult = $courseManager->switchSections($input->sourceId, $input->targetId);
	return $apiResult->toJson();
});


// add section to course
$app->post('/api/courses/{courseId}/sections', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user der autor dieses kurses?

	$input = getRequestObject();

	$section = new Section();
	$section->title = $input->title;
	$courseId = $args["courseId"];
	$courseService = $this->serviceContainer['courseService'];
	$apiResult = $courseService->createSection($courseId, $section);

	return json_encode($apiResult);
});


// upload content to course
$app->post('/api/courses/{courseId}/upload', function ($request, $response, $args) {
	checkIsAuthenticated($this);
	// TODO security check: user needs to be teacher at least

	$uploadDir = $this->serviceContainer['settings']['upload']['upload_path'];
	
	$courseService = $this->serviceContainer['courseService'];
	$courseId = $args["courseId"];
	$course = $courseService->getCourseById($courseId, false);
	$uploadDir .= $course->uuid ."/";
	if (!is_dir($uploadDir)) {
		mkdir($uploadDir);
	}

	$filename = $_FILES['files']['name'];	
	$uploadFile = $uploadDir . $filename;
	if (move_uploaded_file($_FILES['files']['tmp_name'], $uploadFile)) {
		$apiResult = $courseService->addContentFileToCourse($courseId, $filename, $uploadFile);
	} else {
		$apiResult = ApiResultFactory::CreateError('Could not move uploaded file', null);
	}
	return json_encode($apiResult);

});

// get contents of course
$app->get('/api/courses/{courseId}/contents', function ($request, $response, $args) {
	checkIsAuthenticated($this);
	// TODO security check: user needs to be teacher at least

	$contentManager = $this->serviceContainer['contentManager'];
	$courseId = $args["courseId"];
	$contents = $contentManager->getCourseContents($courseId);

	return json_encode(array('data' => $contents));
});
/*
// get apps
$app->get('/api/apps', function ($request, $response, $args) {
	checkIsAuthenticated($this);
	// TODO security check: user needs to be teacher at least

	$appService = $this->serviceContainer['appService'];
	$apps = $appService->getApps();

	return json_encode(array('data' => $apps));
});
*/
// update section
$app->post('/api/sections/{sectionId}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user der autor dieses kurses?

	$input = getRequestObject();

	$section = new Section();
	$section->sectionId = $args["sectionId"];
	$section->title = $input->title;
	$section->description = $input->description;
	$sectionId = $args["sectionId"];
	$courseService = $this->serviceContainer['courseService'];
	$apiResult = $courseService->updateSection($section);

	return $apiResult->toJson();
});


// delete section
$app->delete('/api/sections/{sectionId}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check

	$sectionId = $args["sectionId"];
	$courseService = $this->serviceContainer['courseService'];
	$apiResult = $courseService->deleteSection($sectionId);

	return json_encode($apiResult);

});



// create curriculum based on string
$app->post('/api/courses/{courseId}/curriculum', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user der autor dieses kurses?

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

// create curriculum based on string
$app->delete('/api/courses/{courseId}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	// TODO security check: ist user der autor dieses kurses?
	$courseId = $args['courseId'];

	$courseService = $this->serviceContainer['courseService'];
	
	$apiResult = $courseService->deleteCourse($courseId);
	return $apiResult->toJson();
	
});

// create course
$app->post('/api/courses', function ($request, $response, $args) {
	checkIsAuthenticated($this);


	$courseService = $this->serviceContainer['courseService'];
	$courseJson = getRequestObject();

	$course = new Course();
	$course->setTitle($courseJson->title);
	$course->universityId = 1; //$this->userService->getUser()->universityId;
	$courseService->createCourse($course);
	
	$apiResult = ApiResultFactory::CreateSuccess('A new course was born', $course);
	return json_encode($apiResult);
});


/**
 * creates jwt token for user
 */
function createJwtToken($userId, $key) {
	$token = array(
			'userId' => $userId,
			'iss' => 'savvago',
			'iat' => time(),
			'exp' => time()+86400 // 24 hours
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
		$key = $this->serviceContainer['settings']['security']['tokenKey'];
		$jwt = createJwtToken($user->userId, $key);
		
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


	