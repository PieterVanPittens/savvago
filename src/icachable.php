<?php

/**
 * Models that are supposed to be cached need to implement iCachable
 */
interface iCachable {
	public function getModelType();
	public function getId();
}
?>