<?php
use Pimple\Container;

$serviceContainer = new Container();

// Course
$serviceContainer['courseRepository'] = function ($c) {
	return new courseRepository($c['pdo']);
};
$serviceContainer['courseManager'] = function ($c) {
	return new CourseManager($c['courseRepository'], $c['settings'], $c, $c['serviceCacheRepository']);
};
$serviceContainer['courseService'] = function ($c) {
	$s = new CourseService($c['contextUser'], $c['courseManager'], $c['serviceCacheManager'], $c);
	$s->transactionManager = $c['transactionManager'];
	return $s;
	};

// ServiceCacheManager
$serviceContainer['serviceCacheRepository'] = function ($c) {
	return new ServiceCacheRepository($c['pdo']);
};
$serviceContainer['serviceCacheManager'] = function ($c) {
	return new ServiceCacheManager($c['serviceCacheRepository'], $c['settings']);
};


// User
$serviceContainer['userRepository'] = function ($c) {
	return new UserRepository($c['pdo']);
};
$serviceContainer['displayUserRepository'] = function ($c) {
	return new DisplayUserRepository($c['pdo']);
};
$serviceContainer['userManager'] = function ($c) {
	return new UserManager($c['userRepository'], $c['settings'], $c);
};
$serviceContainer['userService'] = function ($c) {
	$s = new UserService($c['contextUser'], $c['userManager'], $c['serviceCacheManager']);
	$s->transactionManager = $c['transactionManager'];
	return $s;
};
// App
$serviceContainer['appRepository'] = function ($c) {
	return new AppRepository($c['pdo']);
};
$serviceContainer['appManager'] = function ($c) {
	return new AppManager($c['appRepository']);
};
$serviceContainer['appService'] = function ($c) {
	$s = new AppService($c['contextUser'], $c['appManager']);
	$s->transactionManager = $c['transactionManager'];
	return $s;
};
// Lesson
$serviceContainer['lessonRepository'] = function ($c) {
	return new LessonRepository($c['pdo']);
};
$serviceContainer['lessonManager'] = function ($c) {
	return new LessonManager($c['lessonRepository'], $c['settings'], $c);
};
$serviceContainer['lessonService'] = function ($c) {
	$s = new LessonService(
			$c['contextUser']
			, $c['settings']
			, $c['lessonManager']
			, $c['serviceCacheManager']
			, $c['tagManager']
			, $c['tagMatchingManager']
			, $c['userManager']
			, $c['entityStatsManager']
			, $c['storageProvider']
			, $c['contentManager']
			);

	$s->transactionManager = $c['transactionManager'];
	return $s;
};
// Journey
$serviceContainer['journeyRepository'] = function ($c) {
	return new JourneyRepository($c['pdo']);
};
$serviceContainer['journeyManager'] = function ($c) {
	return new JourneyManager($c['journeyRepository'], $c['settings'], $c);
};
$serviceContainer['journeyService'] = function ($c) {
	$s = new JourneyService(
			$c['contextUser']
			, $c['journeyManager']
			, $c['serviceCacheManager']
			, $c['tagManager']
			, $c['tagMatchingManager']
			, $c['entityStatsManager']
			, $c['userManager']
			, $c['settings']);
	$s->transactionManager = $c['transactionManager'];
	return $s;
};
// Tag
$serviceContainer['tagRepository'] = function ($c) {
	return new TagRepository($c['pdo']);
};
$serviceContainer['tagManager'] = function ($c) {
	return new TagManager($c['tagRepository'], $c['settings'], $c);
};

// EntityStats
$serviceContainer['entityStatsRepository'] = function ($c) {
	return new EntityStatsRepository($c['pdo']);
};
$serviceContainer['entityStatsManager'] = function ($c) {
	return new EntityStatsManager($c['entityStatsRepository'], $c['settings'], $c);
};

// Mark
$serviceContainer['markRepository'] = function ($c) {
	return new MarkRepository($c['pdo']);
};
$serviceContainer['markManager'] = function ($c) {
	return new MarkManager($c['markRepository'], $c['settings'], $c);
};
$serviceContainer['markService'] = function ($c) {
	$s = new MarkService($c['contextUser'], $c['markManager'], $c['entityStatsManager'], $c);
	$s->transactionManager = $c['transactionManager'];
	return $s;
};

// Comment
$serviceContainer['commentRepository'] = function ($c) {
	return new CommentRepository($c['pdo']);
};
$serviceContainer['commentManager'] = function ($c) {
	return new CommentManager($c['commentRepository'], $c['settings'], $c);
};
$serviceContainer['commentService'] = function ($c) {
	$s = new CommentService($c['contextUser'], $c['commentManager'], $c['entityStatsManager'], $c['userManager'], $c['settings'], $c);
	$s->transactionManager = $c['transactionManager'];
	return $s;
};



// Content
$serviceContainer['contentRepository'] = function ($c) {
	return new ContentRepository($c['pdo']);
};
$serviceContainer['contentManager'] = function ($c) {
	return new ContentManager($c['contentRepository'] , $c['storageProvider'], $c['settings'], $c);
};

// TransactionManager
$serviceContainer['transactionRepository'] = function ($c) {
	return new TransactionRepository($c['pdo']);
};
$serviceContainer['transactionManager'] = function ($c) {
	return new TransactionManager($c['transactionRepository'], $c['settings'], $c);
};

// TagMatchingManager
$serviceContainer['tagMatchingRepository'] = function ($c) {
	return new TagMatchingRepository($c['pdo']);
};
$serviceContainer['tagMatchingManager'] = function ($c) {
	return new TagMatchingManager($c['tagMatchingRepository'], $c['settings'], $c);
};

// Mail
$serviceContainer['mailQueueRepository'] = function ($c) {
	return new MailQueueRepository($c['pdo']);
};
$serviceContainer['mailQueueManager'] = function ($c) {
	return new MailQueueManager($c['mailQueueRepository'], $c['settings'], $c);
};
// Database
$serviceContainer['pdo'] = function ($c) {
	$db = $c['settings']['db'];
	$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	return $pdo;
};



?>