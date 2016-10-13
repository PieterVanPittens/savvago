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
		$container = $this->app->getContainer()['serviceContainer'];

		// always put application settings into view, e.g. for url rendering
		$this->app->getContainer()['viewData']->data['settings'] = $this->app->getContainer()['settings']['application'];
		
		// this is needed by master
		$this->app->getContainer()['viewData']->data['currentUser'] = $this->app->getContainer()['userContainer']->getUser();
		//$this->app->getContainer()['viewData']->data['categories'] = $container['courseManager']->getCategoriesTree();

		$this->app->getContainer()['viewData']->data['requestUriHost'] = $request->getUri()->getHost();
		$this->app->getContainer()['viewData']->data['requestUriBasePath'] = $request->getUri()->getBasePath();
		
		
		
		
        return $next($request, $response);
	}
}

$app->add(new TemplateMaster($app));
$app->add(new Authenticator($app));
