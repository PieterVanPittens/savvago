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
}
