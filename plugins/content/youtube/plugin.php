<?php

class youtubeContent implements iContentPlugin {

	public function viewContent(ContentObject $content) {
		$youtubeUrl = 'http://www.youtube.com/embed/'.$content->name;
		?>
		<div class="embed-responsive embed-responsive-16by9">
		<iframe class="embed-responsive-item" src="<?= $youtubeUrl; ?>"></iframe>
		</div>
		<?php
	}
	
	public function getThumbnailUrl(ContentObject $content) {
		$url = 'https://img.youtube.com/vi/'.$content->name.'/default.jpg';
		return $url;
	}
	
	public function getImageUrl(ContentObject $content) {
		$url = 'https://img.youtube.com/vi/'.$content->name.'/sddefault.jpg';
		return $url;
	}
	
	public function isValidUrl($url) {
		return true;
	}
			
	public function getContentTypeName() {
		return "youtube";
	}
	
	public function getNameFromUrl($url) {
		// https://www.youtube.com/watch?v=D1qQkZ9mRlo
		$name = "";
		$tokens = explode('?', $url);
		if (isset($tokens[1])) {
			$name = str_replace('v=', '', $tokens[1]);
		}	
		return $name;
	}
}
