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



// require savvago business logic
require '/../src/require.php';
$settings = require __DIR__ . '/../config/config.php';


$app = new \Slim\App($settings);
require '/../src/dependencies.php';




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


// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';
require __DIR__ . '/../src/routes-api.php';




// Run app
$app->run();
