<?php

class jpgContent implements iContentPlugin {
	public function viewContent(ContentObject $content) {
		?>
		<div class="embed-responsive embed-responsive-16by9">
		<img src="<?= $content->url; ?>" width="100%"/>
		</div>
		<?php 
	}
}
