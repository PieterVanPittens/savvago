<?php

class sbsProvider implements iProviderPlugin {

	public function getAssetUrl($guid) {
		$url = "http://localhost/sbs/get.php?guid=$guid";
		return $url;
	}
}