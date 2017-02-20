<?php

class BaseManager {
	/**
	 *
	 * @var PDO Repository
	 */
	protected $repository;

	/**
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 *
	 * @var Container
	 */
	protected $container;

	function __construct($repository, $settings, $container) {
		$this->repository = $repository;
		$this->settings = $settings;
		$this->container = $container;
	}

	/**
	 * checks if parameter is null
	 * @param unknown $parameter
	 * @throws ValidationException
	 */
	function checkParameterForNull($parameter) {
		if (is_null($parameter)) {
			throw new ValidationException("parameter must not be null");
		}
	}

	/**
	 * checks if string is null or empty
	 * @param string $object
	 */
	function isNullOrEmpty($string) {
		if (is_null($string)) {
			return true;
		}
		if ($string == "") {
			return true;
		}
		return false;
	}


}

?>