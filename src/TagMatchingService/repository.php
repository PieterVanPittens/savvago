<?php

/**
 * Repository for matching tags between lessons and journeys
 *
 */
class TagMatchingRepository extends BasePdoRepository {

	/**
	 * gets list of journeys that match a list of tags
	 * journey needs to have all of these tags attached
	 * @param int[] $tagIds
	 */
	public function getMatchingJourneys($tagIds) {
		$models = array();
		if (count($tagIds) == 0) {
			return $models;
		}
	
		$tagIdsIn = implode(',', $tagIds);
	
		$query = '
		select * from journeys where journey_id in (
			select et.entity_id
			from entity_tags et
			where et.entity_type = 7 and et.tag_id in ('.$tagIdsIn.')
			group by et.entity_id
			having count(distinct et.tag_id) = (
				select  count(DISTINCT et1.tag_id)
				from journeys j, entity_tags et1
				where j.journey_id = et1.entity_id and et1.entity_type = 7
				and et1.entity_id = et.entity_id
				group by j.journey_id
				)
			)
			';
	
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		while ($a = $stmt->fetch()) {
			$models[] = Journey::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * gets list of lessons that match a list of tags
	 * lesson needs to have all of these tags attached
	 * @param int[] $tagIds
	 */
	public function getMatchingLessons($tagIds) {
		$models = array();
		if (count($tagIds) == 0) {
			return $models;
		}
	
		$tagIdsIn = implode(',', $tagIds);
	
		$query = '
		select * from lessons where lesson_id in (
			select et.entity_id
			from entity_tags et
			where et.entity_type = 6 and et.tag_id in ('.$tagIdsIn.')
			group by et.entity_id
			having count(distinct et.tag_id) = (
				select  count(DISTINCT et1.tag_id)
				from lessons l, entity_tags et1
				where l.lesson_id = et1.entity_id and et1.entity_type = 6
				and et1.entity_id = et.entity_id
				group by l.lesson_id
				)
			)
			';
	
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		while ($a = $stmt->fetch()) {
			$models[] = Lesson::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
}