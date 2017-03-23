<?php


/**
 * static helpers for rendering views, e.g. formatting dates
 */
abstract class ViewHelper {
	
	/**
	 * renders date
	 * @param int $time timestamp
	 */
	public static function renderDate($time) {
		// how many seconds difference?
		$difference = (time() - $time);
		if ($difference < 60) {
			$difference = round($difference);
			if ($difference == 1) {
				return "$difference second ago";
			} else {
				return "$difference seconds ago";
			}
		}
		// how many minutes difference?
		$difference = (time() - $time) / 60;
		if ($difference < 60) {
			$difference = round($difference);
			if ($difference == 1) {
				return "$difference minute ago";
			} else {
				return "$difference minutes ago";
			}
		}
		// how many hours difference?
		$difference = (time() - $time) / 3600;
		if ($difference < 24) {
			$difference = round($difference);
			if ($difference == 1) {
				return "$difference hour ago";
			} else {
				return "$difference hours ago";
			}
		}
		// how many days difference?
		$difference = (time() - $time) / (3600*24);
		if ($difference < 30) {
			$difference = round($difference);
			if ($difference == 1) {
				return "$difference day ago";
			} else {
				return "$difference days ago";
			}
		}
		// how many months difference?
		$difference = (time() - $time) / (3600*24*30);
		if ($difference < 12) {
			$difference = round($difference);
			if ($difference == 1) {
				return "$difference month ago";
			} else {
				return "$difference months ago";
			}
		}
		// how many years difference?
		$difference = (time() - $time) / (3600*24*30*12);
		if ($difference >= 1) {
			$difference = round($difference);
			if ($difference == 1) {
				return "$difference year ago";
			} else {
				return "$difference years ago";
			}
		}
	}
}

