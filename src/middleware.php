<?php
// Application middleware

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use \Firebase\JWT\JWT;

class Authenticator {

	private $app;

	public function __construct(&$options = array()) {
		$this->app = $options;
	}

	private function getGuestUser() {
		$user = new User();
		$user->userId = 0;
		$user->name = "Guest";
		return $user;
	}
	
	public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) {
		$container = $this->app->getContainer()['serviceContainer'];
		// Authentication based on JWT stored in cookie
		if (isset($_COOKIE['savvago_token'])) {
			$token = $_COOKIE['savvago_token'];
			$key = $this->app->getContainer()['settings']['security']['tokenKey'];
			try {
				$decoded = JWT::decode($token, $key, array('HS256'));
				$user = $container['userManager']->getUserById($decoded->userId);
				if (is_null($user)) {
					$user = $this->getGuestUser();
				}
			} catch (UnexpectedValueException $e) {
				// log because it could be a security attack
				$this->app->getContainer()['logger']->addInfo('JWT UnexpectedValueException. Token: "'.$token.'"');
				// unexpected -> guest
				$user = $this->getGuestUser();
			} catch (Firebase\JWT\ExpiredException $e) {
				// expired -> guest
				$user = $this->getGuestUser();
			} catch (Exception $e) {
				$this->app->getContainer()['logger']->addInfo('unexpected exception. '.$e->getMessage());
				// expired -> guest
				$user = $this->getGuestUser();
			}
		} else {
			// no token -> guest
			$user = $this->getGuestUser();
		}
		$this->app->getContainer()['userContainer']->setUser($user);
		$container['contextUser'] = $user;
        return $next($request, $response);
	}
}


/**
 * adds all data to the app that is required by the template master
 * e.g. currentUser
 */
class TemplateMaster {

	private $app;

	public function __construct(&$options = array()) {
		$this->app = $options;
	}

	
	public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) {
		//$serviceContainer = $this->app->getContainer()['serviceContainer'];
		$container = $this->app->getContainer();

		// always put application settings into view, e.g. for url rendering
		$container['viewData']->data['settings'] = $container['settings']['application'];
		
		// this is needed by master
		$container['viewData']->data['currentUser'] = $container['userContainer']->getUser();

		$container['viewData']->data['requestUriHost'] = $request->getUri()->getHost();
		$container['viewData']->data['requestUriBasePath'] = $request->getUri()->getBasePath();

		
//		$container['settings']['application']
		// set template path from where master.phtml will include views
		// from template folder or app folder
		$templatePath = $container['settings']['renderer']['template_path'];
		
		// check if this is a call to an app and require the routes of that app
		// e.g. request like /public/savvago/apps/content-management
		$relativePath = $_SERVER["SCRIPT_NAME"];
		$relativePath = str_replace('/index.php', '', $relativePath);
		
		$uri = $_SERVER["REQUEST_URI"];
		$uri = str_replace($relativePath.'/', '', $uri);
	/*	
		$tokens = explode('/', $uri);
		$isCallToApp = false;
		$appFolder = '';
		if (count($tokens) > 2) {
			if ($tokens[0] == "apps") {
				$appName = $tokens[1];
				$appFolder = str_replace("index.php", "", __DIR__ . "/../apps/".$appName."/");
				$isCallToApp = true;
				// override path to templates with apps folder
				$templatePath = $appFolder;
			}
		}
*/
		$container['viewData']->data['templatePath'] = $templatePath;
		
        return $next($request, $response);
	}
}


$app->add(new TemplateMaster($app));
$app->add(new Authenticator($app));
