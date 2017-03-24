<?php

/**
 * interface for rendering content, e.g. in lessons
 *
 */
interface iContentPlugin {
	
	/**
	 * renders viewer of contentobject
	 * @param ContentObject $content
	 */
	function viewContent(ContentObject $content);

}