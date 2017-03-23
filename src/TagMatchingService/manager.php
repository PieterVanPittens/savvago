<?php

/**
 * TagMatchingManager
 */
class TagMatchingManager extends BaseManager {

	/**
	 * TagMatchingRepository
	 * @var TagMatchingRepository
	 */
	protected $repository;
	
	/**
	 * gets list of journeys that match a list of tags
	 * journey needs to have all of these tags attached
	 * @param Tag[] $tags
	 */
	public function getMatchingJourneys($tags) {
		$tagIds = array();
		foreach($tags as $tag) {
			$tagIds[] = $tag->tagId;
		}
		$journeys = $this->repository->getMatchingJourneys($tagIds);
		return $journeys;
	}
	
	/**
	 * gets list of lessons that match a list of tags
	 * lesson needs to have all of these tags attached
	 * @param Tag[] $tags
	 */
	public function getMatchingLessons($tags) {
		$tagIds = array();
		foreach($tags as $tag) {
			$tagIds[] = $tag->tagId;
		}
		$lessons = $this->repository->getMatchingLessons($tagIds);
		return $lessons;
	}
	
}