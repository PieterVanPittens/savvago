<?php

/**
 * abstract base Model
 *
 */
class BaseModel {

	/**
	 * getter
	 * @param unknown $property
	 */
	public function __get($property) {
		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}

	/**
	 * setter
	 * @param unknown $property
	 * @param unknown $value
	 */
	public function __set($property, $value) {
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
	}

	/**
	 * converts object to json
	 * @return string
	 */
	public function toJson() {
		$array = (array) $this;
		return json_encode($array);

	}

	/**
	 * Creates a model and populates it with data from a repository row
	 */
	public static function createModelFromRepositoryArray($array) {
		$rc = new ReflectionClass(get_called_class());
		$model = $rc->newInstance();

		// convert database field names to property names
		$properties = array();
		foreach($array as $key => $value) {
			$properties[underscore2Camelcase($key)] = $value;
		}

		foreach($model as $key => $value) {
			if (array_key_exists($key, $properties)) {
				$model->$key = $properties[$key];
			}
		}
		return $model;
	}

	/**
	 * Creates a model and populates it with data from json
	 */
	public static function createModelFromJson($json) {
		$rc = new ReflectionClass(get_called_class());
		$model = $rc->newInstance();

		if ($json == "") {
			return null;
		}

		$jsonObject = json_decode($json);
		if ($jsonObject == null) { // json cannot be parsed
			throw new WebApiException("Request does not contain a valid JSON String");
		}
		foreach ($jsonObject AS $key => $value) {
			$model->{$key} = $value;
		}
		return $model;
	}

	/**
	 * retrieves objectname including name of class
	 */
	public function getObjectName() {
		return get_called_class().$this->name;
	}

	public function getClassName() {
		return get_called_class();
	}
}

?>