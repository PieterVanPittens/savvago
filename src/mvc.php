<?php

// extension of slim MVC framework

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
}
?>