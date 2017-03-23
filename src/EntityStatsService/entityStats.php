<?php

/**
 * EntityStats
 * type of stats that an entity can have
 * enum
 */
abstract class EntityStats {
	const numViews = 1;
	const numLikes = 2;
	
	/**
	 * number of people that checked a station
	 * @var integer
	 */
	const numStationChecks = 3;
	
	const numComments = 4;
}