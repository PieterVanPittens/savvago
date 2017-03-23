<?php

class EntityStat extends BaseModel {
	
	/**
	 * EntityType
	 * @var EntityTypes
	 */
	public $entityType;
	
	public $entityId;
	
	/**
	 * Stats Type
	 * @var EntityStats
	 */
	public $type;
	
	public $value;
}