<?php

/**
 * Mark
 *
 */
class Mark extends BaseModel {

	public $userId;
	/**
	 * EntityType
	 * @var EntityTypes
	 */
	public $entityType;

	public $entityId;
	public $created;
	
	/**
	 * Type
	 * @var MarkTypes
	 */
	public $type;
	
	
}