<?php


interface iProviderPlugin {
	
	/**
	 * creates url to asset
	 * @param unknown $guid
	 */
	function getAssetUrl($guid);
}