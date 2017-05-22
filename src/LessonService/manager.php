<?php

/**
 * LessonManager
*/
class LessonManager extends BaseManager {

	/**
	 * LessonRepository
	 * @var LessonRepository
	 */
	protected $repository;

	/**
	 * gets all lessons
	 * @return array
	 */
	public function getLessons() {
		$lessons = $this->repository->getLessons();
		return $lessons;
	}
	
	/**
	 * creates lesson
	 * @param Lesson $lesson
	 * @return ApiResult
	 */
	public function createLesson($lesson) {
		if ($lesson->title == "") {
			$apiResult = ApiResultFactory::CreateError("Give it a name", null);
		}
		$name = url_slug($lesson->title);
		$lesson->name = $name;
		$nameExists = $this->repository->getLessonByName($name);
		if (!is_null($nameExists)) {
			$apiResult = ApiResultFactory::CreateError("Choose a different title. This one already exists.", $lesson);
			return $apiResult;
		}
		
		$this->repository->createLesson($lesson);
		$apiResult = ApiResultFactory::CreateSuccess("Lesson added", $lesson);
		return $apiResult;		
	}
	
	/**
	 * updates lesson
	 * @param Lesson $lesson
	 */
	public function updateLesson(Lesson $lesson) {
		$this->repository->updateLesson($lesson);
	}
	
	/**
	 * gets one lesson
	 * @param int $lessonId
	 * @return Lesson
	 */
	public function getLesson($lessonId) {
		$lesson = $this->repository->getLessonById($lessonId);
		return $lesson;
	}
	
	/**
	 * gets lesson by name
	 * @param int $name
	 * @return Lesson
	 */
	public function getLessonByName($name) {
		$lesson = $this->repository->getLessonByName($name);
		return $lesson;
	}
	
	/**
	 * deletes a lesson
	 * @param int $lessonId
	 */
	public function deleteLesson($lessonId) {
		$this->repository->deleteLesson($lessonId);
	}
	
	/**
	 * deletes all journey-lesson assignments for one lesson
	 * @param int $lessonId
	 */
	public function deleteJourneyLessons($lessonId) {
		$this->repository->deleteJourneyLessons2($lessonId);
	}

	/**
	 * gets all lessons of a journey
	 * @param int $journeyId
	 * @return Journey[]
	 */
	public function getJourneyLessons($journeyId) {
		$lessons = $this->repository->getJourneyLessons($journeyId);
		return $lessons;
	}

	/**
	 * assigns list of journeys to one lesson
	 * @param Lesson $lesson
	 * @param Journey[] $journeys
	 */
	public function assignJourneysToLesson($lesson, $journeys) {
		$journeyIds = array();
		foreach($journeys as $journey) {
			$journeyIds[] = $journey->journeyId;
		}
	
		// step 1: delete all existing assignments
		$this->repository->deleteLessonJourneys($lesson->lessonId, $journeyIds);
	
		// Step 2: create new ones
		foreach($journeyIds as $journeyId) {
			$this->repository->createLessonJourney($lesson->lessonId, $journeyId);
		}
		
	}
}
