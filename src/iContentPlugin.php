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
	
	/**
	 * checks if an url is a valid url for this plugin
	 * @param unknown $url
	 */
	function isValidUrl($url);
	
	/**
	 * gets name of contenttype of this plugin
	 * @return string
	 */
	function getContentTypeName();
	
	/**
	 * gets name from url, e.g. ID from youtube url
	 * @param string $url
	 * @return string
	 */
	function getNameFromUrl($url);
}