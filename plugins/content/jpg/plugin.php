<?php

class jpgContent implements iContentPlugin {
	public function viewContent(ContentObject $content) {
		?>
		<div class="embed-responsive embed-responsive-16by9">
		<img src="<?= $content->url; ?>" width="100%"/>
		</div>
		<?php 
	}
	
	public function getImageUrl(ContentObject $content) {
		return $content->url;
	}
	
	public function getThumbnailUrl(ContentObject $content) {
		return $content->url;
	}
}
