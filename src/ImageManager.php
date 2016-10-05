<?php

class ImageManager {

	/**
	 * crops image
	 * @param float $x
	 * @param float $y
	 */
	static function cropImage($sourceFile, $targetFile, $x, $y, $width, $height) {
		list($w, $h) = getimagesize($sourceFile);
		
		$imgString = file_get_contents($sourceFile);
		
		
		$image = imagecreatefromstring($imgString);
		$tmp = imagecreatetruecolor($width, $height);
		imagecopyresized($tmp, $image,
		0, 0,
		$x, $y,
		$width, $height,
		$width, $height);
		imagejpeg($tmp, $targetFile, 100);
		
		/* cleanup memory */
		imagedestroy($image);
		imagedestroy($tmp);
	}

	/**
	 * resizes image
	 * @param int $width
	 * @param int $height
	 */
	static function resizeImage($sourceFile, $targetFile, $width, $height){
		if (!file_exists($sourceFile)) {
			throw new ManagerException("Sourcefile '$sourceFile' does not exist");
		}
		/* Get original image x y*/
		list($w, $h) = getimagesize($sourceFile);
		/* calculate new image size with ratio */
		$ratio = max($width/$w, $height/$h);
		$h = ceil($height / $ratio);
		$x = ($w - $width / $ratio) / 2;
		$w = ceil($width / $ratio);
		/* new file name */
		$path = $targetFile;
		/* read binary data from image file */
		$imgString = file_get_contents($sourceFile);
		/* create image from string */
		$image = imagecreatefromstring($imgString);
		$tmp = imagecreatetruecolor($width, $height);
		imagecopyresampled($tmp, $image,
		0, 0,
		0, 0,
		$width, $height,
		$w, $h);
		/* Save image */
		//switch ($_FILES['image']['type']) {
		//case 'image/jpeg':
		imagejpeg($tmp, $path, 100);
		//imagepng($tmp, $path.'.png', 5);
		//break;
		//case 'image/png':
		//	imagepng($tmp, $path, 0);
		//	break;
		//case 'image/gif':
		//	imagegif($tmp, $path);
		//	break;
		//default:
		//	exit;
		//	break;
		//}
		/* cleanup memory */
		imagedestroy($image);
		imagedestroy($tmp);
	}

	/**
	 * resizes image
	 * @param int $width
	 * @param int $height
	 */
	static function resizeImageadsadsad($sourceFile, $targetFile, $width, $height){
		/* Get original image x y*/
		list($w, $h) = getimagesize($sourceFile);
		/* calculate new image size with ratio */
		$ratio = max($width/$w, $height/$h);
		$h = ceil($height / $ratio);
		$x = ($w - $width / $ratio) / 2;
		$w = ceil($width / $ratio);
		/* new file name */
		$path = $targetFile;
		/* read binary data from image file */
		$imgString = file_get_contents($sourceFile);
		/* create image from string */
		$image = imagecreatefromstring($imgString);
		$tmp = imagecreatetruecolor($width, $height);
		imagecopyresized($tmp, $image,
		0, 0,
		$x, 0,
		$width, $height,
		$w, $h);
		/* Save image */
		//switch ($_FILES['image']['type']) {
		//case 'image/jpeg':
		imagejpeg($tmp, $path, 100);
		imagepng($tmp, $path.'.png', 5);
		//break;
		//case 'image/png':
		//	imagepng($tmp, $path, 0);
		//	break;
		//case 'image/gif':
		//	imagegif($tmp, $path);
		//	break;
		//default:
		//	exit;
		//	break;
		//}
		/* cleanup memory */
		imagedestroy($image);
		imagedestroy($tmp);
	}


}

?>