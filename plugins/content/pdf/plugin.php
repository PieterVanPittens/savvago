<?php

class pdfContent implements iContentPlugin {	
	public function viewContent(ContentObject $content) {
		?>
		<div class="embed-responsive embed-responsive-16by9">
		<iframe class="embed-responsive-item" src="<?= $settings['template'] ?>pdf.js/web/viewer.html?file=<?= $content->url; ?>"></iframe>
		</div>
		<?php 
	}
	
	public function getImageUrl(ContentObject $content) {
		return $content->url;
	}
	
	public function getThumbnailUrl(ContentObject $content) {
		return $content->url;
	}
	
	public function isValidUrl($url) {
		return false;
	}

	public function getContentTypeName() {
		return "pdf";
	}
	
	public function getNameFromUrl($url) {
		return "";
	}
	
}

