<?php

/**
 * sets cookie with lastpath that was requested
 * used by login to redirect back to this path after login
 * @param unknown $request
 */
function setLastRequestPath($request) {

//var_dump($request);

//$lastPath = $request->getServerParams()["REDIRECT_SCRIPT_URI"];
$lastPath = $request->getUri()->getBasePath().$request->getUri()->getPath();
//die($lastPath);
	// cannot set path and domain because that does not always work in IE?!
	setcookie("savvago_lastpath", $lastPath, 0, $request->getUri()->getBasePath()); //, 0, $request->getUri()->getBasePath(), $request->getUri()->getHost());
}


/**
 * controller will call this function when access is allowed for logged in users only
 * when user = guest -> login
 */
function showLogin($app, $response, $args) {
	$page = new Page();
	$page->title = 'Login';
	$page->mainView = 'login.phtml';
	$args["page"] = $page;

	return $app->renderer->render($response, 'master.phtml', $args);
}

/**
 * is any user logged in?
 */
function isAuthenticated($app) {
	$user = $app->userContainer->getUser();
	return ($user->userId != 0) && $user->isActive;
}

/**
 * throws UnauthorizedException if noone is logged in
 */
function checkIsAuthenticated($app) {
	if (!isAuthenticated($app)) {
		throw new UnauthorizedException('Please login my friend...');
	}	
}

// course manager
$app->get('/sign-up', function ($request, $response, $args) {
	$page = new Page();
	$page->title = 'Sign Up';
	$page->mainView = 'sign-up.phtml';
	$this->viewData->data["page"] = $page;

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// course manager
$app->get('/teach/{courseId}', function ($request, $response, $args) {
	setLastRequestPath($request);
	
	$page = new Page();
	$page->title = 'Teach Course';
	$page->mainView = 'teach-course.phtml';
	$this->viewData->data["page"] = $page;
	$this->viewData->data['menu'] = 'image';
	
	$courseManager = $this->serviceContainer['courseManager'];

	$this->viewData->data['course'] = $courseManager->getCourseById($args['courseId'], true);

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// section lessons
$app->get('/sections/{sectionId}/lessons', function ($request, $response, $args) {
	
	$section = new Section();
	$section->sectionId = $args['sectionId'];
	$courseManager = $this->serviceContainer['courseManager'];
	$lessons = $courseManager->getSectionLessons($section);
	
	$this->viewData->data['lessons'] = $lessons;	

	return $this->renderer->render($response, 'partial_lessons.phtml', $this->viewData->data);
});

// course manager
$app->get('/teach/{courseId}/curriculum', function ($request, $response, $args) {
	setLastRequestPath($request);
	
	$page = new Page();
	$page->title = 'Teach Course';
	$page->mainView = 'teach-course.phtml';
	$this->viewData->data["page"] = $page;
	$this->viewData->data['menu'] = 'curriculum';
	
	$courseManager = $this->serviceContainer['courseManager'];

	$course = $courseManager->getCourseById($args['courseId'], true);
	$courseManager->loadCurriculum($course);
	$quickEdit = CourseManager::getCurriculumAsString($course);
	$this->viewData->data['course'] = $course;
	$this->viewData->data['quickEdit'] = $quickEdit;

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});


// verify email adress using form
$app->get('/verify', function ($request, $response, $args) {

	$page = new Page();
	$page->title = 'Verify Email';
	$page->mainView = 'verify-form.phtml';
	$this->viewData->data["page"] = $page;
	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// forgot password, set new one
// changing password only possible with valid password key 
$app->get('/newpassword/{passwordKey}', function ($request, $response, $args) {
	$key = $args["passwordKey"];
	$userManager = $this->serviceContainer['userManager'];
	$user = $userManager->getUserByPasswordRecoveryKey($key);
	if (is_null($user)) {
		$page = new Page();
		$page->title = 'Invalid Password Recovery Key';
		$page->mainView = 'new-password_invalid-key.phtml';
		$this->viewData->data["page"] = $page;
		return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
	} else {
		$page = new Page();
		$page->title = 'Set new Password';
		$page->mainView = 'new-password.phtml';
		$this->viewData->data["page"] = $page;
		$this->viewData->data["passwordKey"] = $key;
		$this->viewData->data["user"] = $user;
		return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
	}
});
	

// verify email adress
$app->get('/users/{email}/verify/{key}', function ($request, $response, $args) {
	
	$userManager = $this->serviceContainer['userManager'];

	$user = $userManager->getUserByEmail($args['email']);
	if (is_null($user)) {
		throw new NotFoundException();
	}
	
	$isVerified = $userManager->verifyEmail($user->email, $args['key']);
	$page = new Page();
	$page->title = 'Verify Email';
	$page->mainView = 'verify.phtml';
	$this->viewData->data["page"] = $page;
	$this->viewData->data["isVerified"] = $isVerified;
	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);

});

// user profile
$app->get('/users/{name}', function ($request, $response, $args) {
	setLastRequestPath($request);
	
	$page = new Page();
	$page->title = 'User';
	$page->mainView = 'user.phtml';
	$this->viewData->data["page"] = $page;

	$userManager = $this->serviceContainer['userManager'];
	$courseManager = $this->serviceContainer['courseManager'];
	$user = $userManager->getUserByName($args['name']);
	if (is_null($user)) {
		return $this->renderer->render($response, '404.phtml', $args);
	} else {
		$this->viewData->data['user'] = $user;
		$this->viewData->data['courses'] = $courseManager->getAuthorCourses($user->userId);

		return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
	}	
});

$app->get('/login', function ($request, $response, $args) {
	$page = new Page();
	$page->title = 'Login';
	$page->mainView = 'login.phtml';
	$this->viewData->data['page'] = $page;
	
	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});


$app->get('/forgot', function ($request, $response, $args) {
	$page = new Page();
	$page->title = 'Forgot Password';
	$page->mainView = 'forgot.phtml';
	$this->viewData->data['page'] = $page;

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// INDEX
$app->get('/', function ($request, $response, $args) {
	setLastRequestPath($request);
	$page = new Page();
	$page->title = $this->settings['application']['name'];
	$page->mainView = 'index.phtml';
	$this->viewData->data['page'] = $page;
	$this->viewData->data['journeys'] = $this->serviceContainer['journeyService']->getTopNJourneys(20);

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});


// my courses
$app->get('/my-courses', function ($request, $response, $args) {
	setLastRequestPath($request);
	
	if (!isAuthenticated($this)) {
		return showLogin($this, $response, $this->viewData->data);
	}
	$courseService = $this->serviceContainer['courseService'];
	$courses = 	$courseService->getMyCourses();
	$this->viewData->data["courses"] = $courses;

	$page = new Page();
	$page->title = 'My Courses';
	$page->mainView = 'my-courses.phtml';
	$this->viewData->data["page"] = $page;

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// teach - all courses where I am author
$app->get('/teach', function ($request, $response, $args) {
	setLastRequestPath($request);
	
	if (!isAuthenticated($this)) {
		return showLogin($this, $response, $this->viewData->data);
	}
	$courseService = $this->serviceContainer['courseService'];
	$courses = 	$courseService->getAllAuthorCourses();
	$this->viewData->data["courses"] = $courses;

	$page = new Page();
	$page->title = 'Teach';
	$page->mainView = 'teach.phtml';
	$this->viewData->data["page"] = $page;

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// my settings
$app->get('/my-profile', function ($request, $response, $args) {
	setLastRequestPath($request);
	
	if (!isAuthenticated($this)) {
		return showLogin($this, $response, $this->viewData->data);
	}
	
	$page = new Page();
	$page->title = 'My Settings';
	$page->mainView = 'my-settings.phtml';
	$this->viewData->data["page"] = $page;
	$this->viewData->data['menu'] = 'profile';
	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// my account
$app->get('/my-account', function ($request, $response, $args) {
	setLastRequestPath($request);
	
	if (!isAuthenticated($this)) {
		return showLogin($this, $response, $this->viewData->data);
	}
	
	$page = new Page();
	$page->title = 'My Account';
	$page->mainView = 'my-settings.phtml';
	$this->viewData->data["page"] = $page;
	$$this->viewData->data['menu'] = 'account';

	// this is needed by master
	$this->viewData->data["currentUser"] = $this->userService->getUser();
	$this->viewData->data["categories"] = $this->serviceContainer['courseManager']->getCategoriesTree();

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

$app->get('/my-picture', function ($request, $response, $args) {
	setLastRequestPath($request);
	
	if (!isAuthenticated($this)) {
		return showLogin($this, $response, $this->viewData->data);
	}
	
	$page = new Page();
	$page->title = 'My Picture';
	$page->mainView = 'my-settings.phtml';
	$this->viewData->data["page"] = $page;
	$this->viewData->data['menu'] = 'picture';

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

$app->post('/my-picture', function ($request, $response, $args) {
	if (!isAuthenticated($this)) {
		return showLogin($this, $response, $this->viewData->data);
	}

		var_dump($_POST);
		var_dump($_FILES);
		
		
	$uploadDir = 'C:\\wamp\\www\\lms\\upload\\';
	$uploadFile = $uploadDir  . com_create_guid();
	if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadFile)) {
	
		$cropData = json_decode($_POST["cropdata"]);
		$manager = new ImageManager();
		
		// first: crop
		$targetFile = $uploadFile .'-crop.jpg';
		$manager->cropImage(
		$uploadFile, $targetFile
		, $cropData->x, $cropData->y
		, $cropData->width, $cropData->height
		);
		$sourceFile = $targetFile;
		unlink($uploadFile);
		// then: create all required different sizes

				
			foreach($imageFormats as $key => $imageFormat) {
				$targetFile = $uploadFile .'-'.$key.'.jpg';
				$manager->resizeImage(
					$sourceFile, $targetFile
					, $imageFormat['width'], $imageFormat['height']
					);
			}
			
		} else {
		echo "Möglicherweise eine Dateiupload-Attacke!\n";
	}
		
});

$app->get('/sitemap.xml', function ($request, $response, $args) {
	$this->viewData->data['courses'] = $this->serviceContainer['courseService']->getTopNCourses(20, true);

	$newResponse = $response->withHeader('Content-Type', 'application/xml');
	
	return $this->renderer->render($newResponse, 'sitemap.phtml', $this->viewData->data);
});
