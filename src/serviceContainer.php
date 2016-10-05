<?php
use Pimple\Container;

$serviceContainer = new Container();

// University
$serviceContainer['universityRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new universityRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['universityManager'] = function ($c) {
	return new UniversityManager($c['universityRepository'], $c['settings'], $c);
};
$serviceContainer['universityService'] = function ($c) {
	return new UniversityService($c['contextUser'], $c['universityRepository'], $c['settings'], $c);
};

// Course
$serviceContainer['courseRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new courseRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['courseManager'] = function ($c) {
	return new CourseManager($c['courseRepository'], $c['settings'], $c);
};
$serviceContainer['courseService'] = function ($c) {
	return new CourseService($c['contextUser'], $c['courseRepository'], $c['settings'], $c);
};

// User
$serviceContainer['userRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new UserRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['userManager'] = function ($c) {
	return new UserManager($c['userRepository'], $c['settings'], $c);
};
$serviceContainer['userService'] = function ($c) {
	return new UserService($c['contextUser'], $c['userRepository'], $c['settings'], $c);
};

// Content
$serviceContainer['contentRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new ContentRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['contentManager'] = function ($c) {
	return new ContentManager($c['contentRepository'], $c['settings'], $c);
};

// Mail
$serviceContainer['mailQueueRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new MailQueueRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['mailQueueManager'] = function ($c) {
	return new MailQueueManager($c['mailQueueRepository'], $c['settings'], $c);
};

?>