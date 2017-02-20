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
	return new CourseManager($c['courseRepository'], $c['settings'], $c, $c['serviceCacheRepository']);
};
$serviceContainer['courseService'] = function ($c) {
	return new CourseService($c['contextUser'], $c['courseManager'], $c['serviceCacheManager'], $c);
};

// ServiceCacheManager
$serviceContainer['serviceCacheRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new ServiceCacheRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['serviceCacheManager'] = function ($c) {
	return new ServiceCacheManager($c['serviceCacheRepository'], $c['settings']);
};


// User
$serviceContainer['userRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new UserRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['displayUserRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new DisplayUserRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['userManager'] = function ($c) {
	return new UserManager($c['userRepository'], $c['settings'], $c);
};
$serviceContainer['userService'] = function ($c) {
	return new UserService($c['contextUser'], $c['userManager'], $c['serviceCacheManager']);
};
// App
$serviceContainer['appRepository'] = function ($c) {
	$db = $c['settings']['db'];
	return new AppRepository($db['host'], $db['dbname'], $db['user'], $db['pass']);
};
$serviceContainer['appManager'] = function ($c) {
	return new AppManager($c['appRepository']);
};
$serviceContainer['appService'] = function ($c) {
	return new AppService($c['appManager']);
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