<?php

/**
 * Tag
 *
 */
class Tag extends BaseModel implements iEntity {
	public $tagId;
	public $name;
	
	public function getEntityType() {
		return ModelTypes::Tag;
	}
	public function getId() {
		return $this->tagId;
	}
}

?>