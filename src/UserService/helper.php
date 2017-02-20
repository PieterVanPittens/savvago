<?php

/*
 * strips first line from text
 */
function stripFirstLine($text)
{
	return substr( $text, strpos($text, "\n")+1 );
}

/*
 * gets first line of text
 */
function getFirstLine($text) {
	return strtok($text, "\n"); // subject = first line of email template
}


function getGUID(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
	}else{
		mt_srand((double)microtime()*10000);
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		.substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		.chr(125);// "}"
		return $uuid;
	}
}

?>