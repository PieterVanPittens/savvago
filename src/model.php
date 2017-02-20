<?php
/**
 * converts underscore string to Camelcase string
 * translates database field names to object propery names
 */
function underscore2Camelcase($str) {
	$words = explode('_', strtolower($str));

	$return = '';
	foreach ($words as $word) {
		$return .= ucfirst(trim($word));
	}
	$return = lcfirst($return);
	return $return;
}


class GrapesException extends Exception {
	/**
	 * Message
	 * @var Message
	 */
	public $apiMessage;
	
	
	function __construct($text) {
		$this->setError($text);
	}
	
	/**
	 * sets an error message to this exception
	 * @param string $text
	 */
	function setError($text) {
		$this->apiMessage = new Message();
		$this->apiMessage->type = MessageTypes::Error;
		$this->apiMessage->text = $text;
	}
	

}

class BusinessException extends GrapesException {
}

class ModelException extends GrapesException {

	public $modelErrors = array();

	public function addModelError($propertyName, $message) {
		$this->modelErrors[$propertyName] = $message;
	}

	public function hasModelErrors() {
		return count($this->modelErrors) > 0;
	}
}
class RepositoryException extends GrapesException {
}
class ManagerException extends GrapesException {
}
class PluginException extends GrapesException {
}
class WebApiException extends GrapesException {
}
class ValidationException extends GrapesException {
}
/**
 * Ressource was not found
 * this exception will result in 404 error code in API Response
 *
 */
class NotFoundException extends GrapesException {
}
/**
 * will result in 401 error code in API Response*/
class UnauthorizedException extends GrapesException {
	
}




class ModelHelper {

	/**
	 * converts title to name
	 * @param string $title
	 * @return string
	 */
	static function convertTitleToName($title) {
		$name = preg_replace('/^-+|-+$/', '', strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title)));
		return $name;
	}
}




/**
 * Page
 */
class Page {
	public $title;
	public $mainView;
}







/**
 * ModelTypes
 * enum
 */
abstract class ModelTypes {
	const Course = 1;
}


?>