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
	
	/**
	 * gets url for thumbnail 
	 * @param ContentObject $content
	 */
	function getThumbnailUrl(ContentObject $content);

	/**
	 * gets url for image of this content
	 * @param ContentObject $content
	 */
	function getImageUrl(ContentObject $content);
	
}