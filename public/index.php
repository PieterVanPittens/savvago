<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

Use Slim\Http\Response;

require __DIR__ . '/../vendor/autoload.php';


require __DIR__ . '/../vendor/php-jwt-3.0.0/src/JWT.php';
require __DIR__ . '/../vendor/php-jwt-3.0.0/src/BeforeValidException.php';
require __DIR__ . '/../vendor/php-jwt-3.0.0/src/SignatureInvalidException.php';
require __DIR__ . '/../vendor/php-jwt-3.0.0/src/ExpiredException.php';

require __DIR__ . '/../vendor/url_slug/url_slug.php';
require __DIR__ . '/../vendor/parsedown/Parsedown.php';


$settings = require __DIR__ . '/../src/settings.php';



// settings are complete and we can instantiate the app
$app = new \Slim\App($settings);




$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
	return function ($request, $response, $exception) use ($c) {

		$className = get_class($exception);

		switch ($className) {
			case "NotFoundException":
				$apiResult = new ApiResult();
				$apiResult->setError($exception->apiMessage->text);
				return $c['response']
				->withHeader('Content-Type', 'application/json')
				->withStatus(404)
				->write($apiResult->toJson());
				break;				
			case "ValidationException":
				$apiResult = new ApiResult();
				$apiResult->setError($exception->apiMessage->text);
				return $c['response']
				->withHeader('Content-Type', 'application/json')
				->withStatus(400)
				->write($apiResult->toJson());
				break;				
			case "UnauthorizedException":
				$apiResult = new ApiResult();
				$apiResult->setError($exception->apiMessage->text);
				return $c['response']
				->withHeader('Content-Type', 'application/json')
				->withStatus(401)
				->write($apiResult->toJson());
				break;
			default:
				return $c['response']->withStatus(500)
				->withHeader('Content-Type', 'text/html')
				->write($exception);
		}

	};
};

// load savvago stuff
require __DIR__ . '/../src/imodel.php';
require __DIR__ . '/../src/basemodel.php';
require __DIR__ . '/../src/icachable.php';
require __DIR__ . '/../src/basemanager.php';
require __DIR__ . '/../src/baseservice.php';
require __DIR__ . '/../src/basepdorepository.php';
require __DIR__ . '/../src/model.php';
require __DIR__ . '/../src/ImageManager.php';

require __DIR__ . '/../src/UserService/helper.php';
require __DIR__ . '/../src/UserService/manager.php';
require __DIR__ . '/../src/UserService/repository.php';
require __DIR__ . '/../src/UserService/role.php';
require __DIR__ . '/../src/UserService/service.php';
require __DIR__ . '/../src/UserService/user.php';
require __DIR__ . '/../src/UserService/usertypes.php';
require __DIR__ . '/../src/UserService/usercontainer.php';

require __DIR__ . '/../src/ContentService/contentobject.php';
require __DIR__ . '/../src/ContentService/contenttype.php';
require __DIR__ . '/../src/ContentService/manager.php';
require __DIR__ . '/../src/ContentService/repository.php';

require __DIR__ . '/../src/UniversityService/manager.php';
require __DIR__ . '/../src/UniversityService/repository.php';
require __DIR__ . '/../src/UniversityService/service.php';
require __DIR__ . '/../src/UniversityService/university.php';

require __DIR__ . '/../src/CourseService/category.php';
require __DIR__ . '/../src/CourseService/course.php';
require __DIR__ . '/../src/CourseService/enrollment.php';
require __DIR__ . '/../src/CourseService/lesson.php';
require __DIR__ . '/../src/CourseService/manager.php';
require __DIR__ . '/../src/CourseService/progress.php';
require __DIR__ . '/../src/CourseService/progresstypes.php';
require __DIR__ . '/../src/CourseService/repository.php';
require __DIR__ . '/../src/CourseService/section.php';
require __DIR__ . '/../src/CourseService/service.php';

require __DIR__ . '/../src/ServiceCache/manager.php';
require __DIR__ . '/../src/ServiceCache/repository.php';


require __DIR__ . '/../src/MailService/mail.php';

require __DIR__ . '/../src/AppService/app.php';
require __DIR__ . '/../src/AppService/manager.php';
require __DIR__ . '/../src/AppService/repository.php';
require __DIR__ . '/../src/AppService/service.php';

require __DIR__ . '/../src/displayUser.php';
require __DIR__ . '/../src/mvc.php';
require __DIR__ . '/../src/helpers.php';


// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';
require __DIR__ . '/../src/routes-api.php';




// Run app
$app->run();
