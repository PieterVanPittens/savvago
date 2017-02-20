<?php

/**
 * Category
 *
 */
class Category extends BaseModel implements iModel {
	public $categoryId;
	public $name;
	public $title;
	public $parentId;
	public $ranking;
	public $categories = array();
	public $urls = array();

	public function getId() {
		return $this->categoryId;
	}
}
?>