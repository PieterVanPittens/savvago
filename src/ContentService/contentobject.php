<?php
/**
 * ContentObject
 */
class ContentObject extends BaseModel {
	public $objectId;
	public $typeId;
	public $content;
	public $name;
	public $md5Hash;
	
	/**
	 * ContentType
	 * @var ContentType
	 */
	public $type;
	
	public $url;
}
?>
