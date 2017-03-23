<?php

/**
 * enum MarkTypes
 *
 */
abstract class MarkTypes {
	/**
	 * user has liked an entity, e.g. journey or comment
	 * @var integer
	 */
	const Like = 1;
	/**
	 * user has checked an entity, e.g. station
	 * @var integer
	 */
	const Check = 2;
	
	/**
	 * user has viewed an entity, e.g. station
	 * @var integer
	 */
	const View = 3;
	
	
}