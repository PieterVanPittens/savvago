<?php
/**
 * ContentType
 */
class ContentType extends BaseModel {
	public $typeId;
	public $name;
	
	/**
	 * @var ContentSourceTypes
	 */
	public $source;
	public $extension;
}

?>
