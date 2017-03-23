<?php

/**
 * TagManager
*/
class TagManager extends BaseManager {

	/**
	 * TagRepository
	 * @var TagRepository
	 */
	protected $repository;
	
	/**
	 * splits string of tagNames separated by space into array of tagNames
	 * @param string $tagsString
	 * @return string[]
	 */
	public function splitTagNames($tagsString) {
		$tagsString = trim($tagsString);
		$tagNames = explode(" ", $tagsString);
		$tagNames = array_unique($tagNames);
		return $tagNames;
	}
	
	/**
	 * gets array of Tags by their names
	 * @param string[] $tagNames
	 * @return Tag[]
	 */
	public function getTagsByNames($tagNames) {
		$tags = $this->repository->getTagsByNames($tagNames);
		return $tags;
	}
	
	/**
	 * Saves Tags from string
	 * @param string $tagsString
	 * @return Tag[]
	 */
	public function saveTags($tagsString) {
		$tagNames = $this->splitTagNames($tagsString);

		$tags = array();
		foreach($tagNames as $tagName) {
			if (strlen($tagName) < 3) {
				throw new ValidationException('tags', 'Tags must be at least 3 Characters long.');
			}
			$tag = $this->repository->getTagByName($tagName);
			if (is_null($tag)) {
				$tag = new Tag();
				$tag->name = $tagName;
				$this->repository->createTag($tag);
			}
			$tags[] = $tag;
		}
		return $tags;
	}
	
	/**
	 * assigns tags to one entity
	 * @param Tag[] $tags
	 * @param iEntity $entity
	 */
	public function assignTagsToEntity($tags, iEntity $entity) {
		foreach($tags as $tag) {
			if (!$this->repository->existsEntityTag($tag->tagId, $entity->getEntityType(), $entity->getId())) {
				$this->repository->createEntityTag($tag->tagId, $entity->getEntityType(), $entity->getId());
			}
		}
	}
	
	/**
	 * deletes 
	 * @param EntityTypes $entityType
	 * @param int $journeyId
	 */
	public function deleteEntityTags($entityType, $journeyId) {
		$this->repository->deleteEntityTags($entityType, $journeyId);
	}
	
}