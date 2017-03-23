<?php

// extension of slim MVC framework

/**
 * ApiResultFactory
 */
class ApiResultFactory {
	/** 
	 * creates a success result
	 */
	public static function createSuccess($message, $object) {
		$result = new ApiResult();
		$result->setSuccess($message);
		$result->object = $object;
		return $result;
	}
	/** 
	 * creates a warning result
	 */
	public static function createWarning($message, $object) {
		$result = new ApiResult();
		$result->setWarning($message);
		$result->object = $object;
		return $result;
	}
	/** 
	 * creates an error result
	 */
	public static function createError($message, $object) {
		$result = new ApiResult();
		$result->setError($message);
		$result->object = $object;
		return $result;
	}
	
	/**
	 * creates ApiResult from ValidationException
	 * @param ValidationException $exception
	 * @return ApiResult
	 */
	public static function createErrorFromValidationException(ValidationException $exception) {
		$result = new ApiResult();
		$message = new Message();
		$message->type = MessageTypes::Error;
		$message->text = $exception->message;
		$message->AddPropertyMessage($exception->propertyName, $exception->message);
		$result->message = $message;
		$result->object = null;
		return $result;
	}
}


/**
 * Api Result
 * result of call to REST-Api
 */
class ApiResult {
	/**
	 * Message
	 * @var Message
	 */
	public $message;
	public $object;
	
	public function toJson() {
		return json_encode($this);
	}
	
	/**
	 * sets an error message to this exception
	 * @param string $text
	 */
	function setError($text) {
		$this->message = new Message();
		$this->message->type = MessageTypes::Error;
		$this->message->text = $text;
	}
	
	/**
	 * sets a warning message to this exception
	 * @param string $text
	 */
	function setWarning($text) {
		$this->message = new Message();
		$this->message->type = MessageTypes::Warning;
		$this->message->text = $text;
	}
	
	/**
	 * sets a success message to this exception
	 * @param string $text
	 */
	function setSuccess($text) {
		$this->message = new Message();
		$this->message->type = MessageTypes::Success;
		$this->message->text = $text;
	}
	
	
}

abstract class MessageTypes {
	const Success = 1;
	const Warning = 2;
	const Error = 3;
}

/**
 * container for all data is required for rendering a view
 */
class ViewData {
	public $data = array();
}

/**
 * Message
 */
class Message {
	/**
	 * Message Type
	 * @var MessageTypes
	 */
	public $type = MessageTypes::Success;
	public $text;
	
	/**
	 * key value pairs of property names and validation messages
	 * @var array
	 */
	public $propertyMessages = array();

	/**
	 * adds validation message text to propertyName
	 * @param string $propertyName
	 * @param string $message
	 */
	public function AddPropertyMessage($propertyName, $message) {
		$this->propertyMessages[$propertyName] = $message;
	}
}
?>