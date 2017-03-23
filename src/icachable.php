<?php

/**
 * Models that represent an entity in the system need to implement iEntity
 * that will make them cachable, taggable, registerable, exportable, etc.
 */
interface iEntity {
	public function getEntityType();
	public function getId();
}
?>