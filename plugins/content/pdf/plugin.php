<?php

class pdfContent implements iContentPlugin {	
	public function viewContent(ContentObject $content) {
		?>
		<div class="embed-responsive embed-responsive-16by9">
		<iframe class="embed-responsive-item" src="<?= $settings['template'] ?>pdf.js/web/viewer.html?file=<?= $content->url; ?>"></iframe>
		</div>
		<?php 
	}
}

