<?php


/**
 * App Service
 */
class AppService extends BaseService {


	/**
	 * AppManager
	 * @var AppManager
	 */
	private $manager;
	
	/**
	 * ContextUser
	 * @var User
	 */
	private $contextUser;

	/**
	 * constructor
	 * @param User $contextUser
	 * @param AppManager $manager
	 */
	function __construct(User $contextUser, AppManager $manager) {
		$this->manager = $manager;
		$this->contextUser = $contextUser;
	}

	/**
	 * gets all apps
	 */
	public function getApps() {
		return $this->manager->getApps();
	}
	/**
	 * gets all apps that are required for one homescreen
	 */
	public function getHomeApps() {
		// todo: security check: does this user have this role?

		$apps = array();
		if ($this->contextUser->type == UserTypes::Admin) {
			$a = $this->manager->getRoleApps(UserTypes::Student);
			$apps = array_merge($apps, $a);
			$a = $this->manager->getRoleApps(UserTypes::Teacher);
			$apps = array_merge($apps, $a);
			$a = $this->manager->getRoleApps(UserTypes::Admin);
			$apps = array_merge($apps, $a);
		}
		else if ($this->contextUser->type == UserTypes::Teacher) {
			$a = $this->manager->getRoleApps(UserTypes::Student);
			$apps = array_merge($apps, $a);
			$a = $this->manager->getRoleApps(UserTypes::Teacher);
			$apps = array_merge($apps, $a);
		}
		else if ($this->contextUser->type == UserTypes::Student) {
			$a = $this->manager->getRoleApps(UserTypes::Student);
			$apps = array_merge($apps, $a);
		}
		
		return $apps;
	}
	
	/**
	 * gets app by name
	 * @param string $name
	 * @return App
	 */
	public function getAppByName($name) {
		// todo: security check
		
		$app = $this->manager->getAppByName($name);
		if ($app == null) {
			throw new NotFoundException("App $name does not exist");
		}
		return $app;
	}
}

?>