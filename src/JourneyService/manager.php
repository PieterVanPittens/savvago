<?php

/**
 * JourneyManager
*/
class JourneyManager extends BaseManager {

	/**
	 * JourneyRepository
	 * @var JourneyRepository
	 */
	protected $repository;

	/**
	 * creates Journey
	 * @param Journey $journey
	 * @return ApiResult
	 */
	public function createJourney($journey) {
		if ($journey->title == "") {
			$apiResult = ApiResultFactory::CreateError("Cannot create this journey", null);
			$apiResult->message->AddPropertyMessage("title", "Give it a name");
		}
		$name = url_slug($journey->title);
		$journey->name = $name;
		$nameExists = $this->repository->getJourneyByName($name);
		if (!is_null($nameExists)) {
			$apiResult = ApiResultFactory::CreateError("Cannot create this journey", $journey);
			$apiResult->message->AddPropertyMessage("title", "Choose a different title. This one already exists.");
				return $apiResult;
		}

		$this->repository->createJourney($journey);
		$apiResult = ApiResultFactory::CreateSuccess("Journey added", $journey);
		return $apiResult;
	}
	
	/**
	 * gets all journeys
	 * @return Journey[]
	 */
	public function getJourneys() {
		$journeys = $this->repository->getJourneys();

		return $journeys;
	}
	
	/**
	 * gets one journey
	 * @param int $journeyId
	 * @return Journey
	 */
	public function getJourney($journeyId) {
		$journey = $this->repository->getJourney($journeyId);
		return $journey;
	}
	
	/**
	 * deletes a journey
	 * @param int $journeyId
	 */
	public function deleteJourney($journeyId) {
		$this->repository->deleteJourney($journeyId);
	}

	/**
	 * updates journey
	 * @param Journey $journey
	 */
	public function updateJourney(Journey $journey) {
		$this->repository->updateJourney($journey);
	}
	
	
	/**
	 * deletes all journey-lesson assignments for one journey
	 * @param int $journeyId
	 */
	public function deleteJourneyLessons($journeyId) {
		$this->repository->deleteJourneyLessons2($journeyId);
	}
	
	
	/**
	 * assigns list of lessons to one journey
	 * @param Journey $journey
	 * @param Lesson[] $lessons
	 */
	public function assignLessonsToJourney($journey, $lessons) {
		$lessonIds = array();
		foreach($lessons as $lesson) {
			$lessonIds[] = $lesson->lessonId;
		}
		
		// step 1: delete all existing assignments
		$this->repository->deleteJourneyLessons($journey->journeyId, $lessonIds);
		
		// Step 2: create new ones
		foreach($lessonIds as $lessonId) {
			$this->repository->createJourneyLesson($journey->journeyId, $lessonId);
		}
	}
	
	/**
	 * gets top n journeys based on num enrollments
	 * @param int $userId
	 * @param int $n
	 * @return Journey[]
	 */
	public function getTopNJourneys($userId, $n) {
		$journeys = $this->repository->getTopNJourneys($userId, $n);
		return $journeys;
	}

	/**
	 * gets one journey by name
	 * @param int $name
	 * @return Journey
	 */
	public function getJourneyByName($name) {
		$journey = $this->repository->getJourneyByName($name);
		return $journey;
	}
	
}
