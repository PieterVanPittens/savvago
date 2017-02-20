<?php

/**
 * just a container, required for handling of currentuser in slimphp layer
 *
 */
class UserContainer {
	private $user;

	public function getUser() {
		return $this->user;
	}

	public function setUser($user) {
		$this->user = $user;
	}

	public function getGuest() {
		$user = new User();
		$user->userId = 0;
		$user->name = "Guest";
		$user->displayName = "Guest";
		return $user;
	}
}

?>