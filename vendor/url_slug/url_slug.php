<?php
/**
 * Create a web friendly URL slug from a string.
 * http://iamseanmurphy.com/creating-seo-friendly-urls-in-php-with-url-slug/
 
 * Although supported, transliteration is discouraged because
 *     1) most web browsers support UTF-8 characters in URLs
 *     2) transliteration causes a loss of information
 *
 * @author Sean Murphy <sean@iamseanmurphy.com>
 * @copyright Copyright 2012 Sean Murphy. All rights reserved.
 * @license http://creativecommons.org/publicdomain/zero/1.0/
 *
 * @param string $str
 * @param array $options
 * @return string
 */
function url_slug($str, $options = array()) {
	// Make sure string is in UTF-8 and strip invalid UTF-8 characters
	$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
	
	$defaults = array(
		'delimiter' => '-',
		'limit' => null,
		'lowercase' => true,
		'replacements' => array(),
		'transliterate' => false,
	);
	
	// Merge options
	$options = array_merge($defaults, $options);
	
	$char_map = array(
		// Latin
		'�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'AE', '�' => 'C', 
		'�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'I', 
		'�' => 'D', '�' => 'N', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', 'O' => 'O', 
		'�' => 'O', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'U', 'U' => 'U', '�' => 'Y', '�' => 'TH', 
		'�' => 'ss', 
		'�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'ae', '�' => 'c', 
		'�' => 'e', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i', 
		'�' => 'd', '�' => 'n', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', 'o' => 'o', 
		'�' => 'o', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'u', 'u' => 'u', '�' => 'y', '�' => 'th', 
		'�' => 'y',
		// Latin symbols
		'�' => '(c)',
		// Greek
		'?' => 'A', '?' => 'B', 'G' => 'G', '?' => 'D', '?' => 'E', '?' => 'Z', '?' => 'H', 'T' => '8',
		'?' => 'I', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => '3', '?' => 'O', '?' => 'P',
		'?' => 'R', 'S' => 'S', '?' => 'T', '?' => 'Y', 'F' => 'F', '?' => 'X', '?' => 'PS', 'O' => 'W',
		'?' => 'A', '?' => 'E', '?' => 'I', '?' => 'O', '?' => 'Y', '?' => 'H', '?' => 'W', '?' => 'I',
		'?' => 'Y',
		'a' => 'a', '�' => 'b', '?' => 'g', 'd' => 'd', 'e' => 'e', '?' => 'z', '?' => 'h', '?' => '8',
		'?' => 'i', '?' => 'k', '?' => 'l', '�' => 'm', '?' => 'n', '?' => '3', '?' => 'o', 'p' => 'p',
		'?' => 'r', 's' => 's', 't' => 't', '?' => 'y', 'f' => 'f', '?' => 'x', '?' => 'ps', '?' => 'w',
		'?' => 'a', '?' => 'e', '?' => 'i', '?' => 'o', '?' => 'y', '?' => 'h', '?' => 'w', '?' => 's',
		'?' => 'i', '?' => 'y', '?' => 'y', '?' => 'i',
		// Turkish
		'S' => 'S', 'I' => 'I', '�' => 'C', '�' => 'U', '�' => 'O', 'G' => 'G',
		's' => 's', 'i' => 'i', '�' => 'c', '�' => 'u', '�' => 'o', 'g' => 'g', 
		// Russian
		'?' => 'A', '?' => 'B', '?' => 'V', '?' => 'G', '?' => 'D', '?' => 'E', '?' => 'Yo', '?' => 'Zh',
		'?' => 'Z', '?' => 'I', '?' => 'J', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => 'O',
		'?' => 'P', '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'U', '?' => 'F', '?' => 'H', '?' => 'C',
		'?' => 'Ch', '?' => 'Sh', '?' => 'Sh', '?' => '', '?' => 'Y', '?' => '', '?' => 'E', '?' => 'Yu',
		'?' => 'Ya',
		'?' => 'a', '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'yo', '?' => 'zh',
		'?' => 'z', '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n', '?' => 'o',
		'?' => 'p', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c',
		'?' => 'ch', '?' => 'sh', '?' => 'sh', '?' => '', '?' => 'y', '?' => '', '?' => 'e', '?' => 'yu',
		'?' => 'ya',
		// Ukrainian
		'?' => 'Ye', '?' => 'I', '?' => 'Yi', '?' => 'G',
		'?' => 'ye', '?' => 'i', '?' => 'yi', '?' => 'g',
		// Czech
		'C' => 'C', 'D' => 'D', 'E' => 'E', 'N' => 'N', 'R' => 'R', '�' => 'S', 'T' => 'T', 'U' => 'U', 
		'�' => 'Z', 
		'c' => 'c', 'd' => 'd', 'e' => 'e', 'n' => 'n', 'r' => 'r', '�' => 's', 't' => 't', 'u' => 'u',
		'�' => 'z', 
		// Polish
		'A' => 'A', 'C' => 'C', 'E' => 'e', 'L' => 'L', 'N' => 'N', '�' => 'o', 'S' => 'S', 'Z' => 'Z', 
		'Z' => 'Z', 
		'a' => 'a', 'c' => 'c', 'e' => 'e', 'l' => 'l', 'n' => 'n', '�' => 'o', 's' => 's', 'z' => 'z',
		'z' => 'z',
		// Latvian
		'A' => 'A', 'C' => 'C', 'E' => 'E', 'G' => 'G', 'I' => 'i', 'K' => 'k', 'L' => 'L', 'N' => 'N', 
		'�' => 'S', 'U' => 'u', '�' => 'Z',
		'a' => 'a', 'c' => 'c', 'e' => 'e', 'g' => 'g', 'i' => 'i', 'k' => 'k', 'l' => 'l', 'n' => 'n',
		'�' => 's', 'u' => 'u', '�' => 'z'
	);
	
	// Make custom replacements
	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
	
	// Transliterate characters to ASCII
	if ($options['transliterate']) {
		$str = str_replace(array_keys($char_map), $char_map, $str);
	}
	
	// Replace non-alphanumeric characters with our delimiter
	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	
	// Remove duplicate delimiters
	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	
	// Truncate slug to max. characters
	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
	
	// Remove delimiter from ends
	$str = trim($str, $options['delimiter']);
	
	return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
}
?>