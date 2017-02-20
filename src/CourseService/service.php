<?php

/**
 * Course Service
 */
class CourseService extends BaseService {

	/**
	 * ContextUser
	 * @var User
	 */
	private $contextUser;

	/**
	 * @var CourseManager
	 */
	private $manager;

	/**
	 * @var ServiceCacheManager
	 */
	private $serviceCacheManager;

	/**
	 * constructor
	 * @param User $contextUser
	 * @param CourseManager $manager
	 * @param ServiceCacheManager $serviceCacheManager
	 */
	function __construct($contextUser, $manager, $serviceCacheManager) {
		$this->contextUser = $contextUser;
		$this->manager = $manager;
		$this->serviceCacheManager = $serviceCacheManager;
	}

	/**
	 * publishes course
	 * @param int $courseId
	 * @param bool $isPublished
	 */
	public function publishCourse($courseId, $isPublished) {
		// todo: security check
		$this->manager->publishCourse($courseId, $isPublished);
	}


	/**
	 * gets top n courses based on num enrollments
	 * @param int $n
	 * @param bool $withContentObjects
	 * @return array
	 */
	public function getTopNCourses($n, $withContentObjects) {
		// todo: security check
		return $this->manager->getTopNCourses($this->contextUser->userId, $n, $withContentObjects);
	}

	/**
	 * imports course from zip file
	 * @param User $user
	 * @param int universityId
	 * @param string filename (zip file)
	 * @return ApiResult
	 */
	function importCourse($filename) {
		$universityId = 0;
		try {
			$result = $this->manager->importCourse($this->contextUser, $universityId, $filename);
			if ($result->message->type == MessageTypes::Success) {
				$this->serviceCacheManager->deleteCaches($result->object);
			}
		} catch (ManagerException $ex) {
			$result = ApiResultFactory::CreateError($ex->apiMessage->text, null);
		}
		return $result;
	}

	/**
	 * enrolls this user to a course
	 * @param Course $course
	 */
	public function enrollToCourse($course) {
		$this->manager->enrollToCourse($this->contextUser, $course);
	}

	/**
	 * checks if user is enrolled to course
	 * @param Course $course
	 * @return bool
	 */
	public function isEnrolled($course) {
		return $this->manager->isEnrolled($this->contextUser, $course);
	}

	/**
	 * gets all courses that a this user is enrolled to
	 * @return array
	 */
	public function getMyCourses() {
		return $this->manager->getEnrolledCourses($this->contextUser);
	}

	/**
	 * finishes a lesson
	 * @param Lesson $lesson
	 */
	public function finishLesson($lesson) {
		$this->manager->finishLesson($this->contextUser, $lesson);
		$apiResult = new ApiResult();
		$apiResult->setSuccess("lesson finished");
		return $apiResult;

	}

	/**
	 * loads all progress of this user in a course
	 * @param Course $course
	 * @return array
	 */
	public function loadCourseProgresses($course) {
		$this->manager->loadCourseProgresses($this->contextUser, $course);
	}

	/**
	 * completely deletes a course
	 * @param int courseId
	 * @return ApiResult
	 */
	function deleteCourse($courseId) {
		// todo: security check
		$apiResult = $this->manager->deleteCourse($courseId);
		return $apiResult;
	}

	/**
	 * deletes a section
	 * @param int sectionId
	 * @return ApiResult
	 */
	function deleteSection($sectionId) {
		// todo: security check
		$apiResult = $this->manager->deleteSection($sectionId);
		return $apiResult;
	}

	/**
	 * updates a section
	 * @param Section $section
	 * @return ApiResult
	 */
	function updateSection($section) {
		// todo: security check
		$apiResult = $this->manager->updateSection($section);
		return $apiResult;
	}

	/**
	 * creates section
	 * @param int $courseId
	 * @param Section $section
	 */
	public function createSection($courseId, $section) {
		// todo: security checks
		if ($section->title == "") {
			$apiResult = ApiResultFactory::CreateError("Give it a name", null);
		}
		$this->manager->createSection($courseId, $section);
		$apiResult = ApiResultFactory::CreateSuccess("Section added", $section);
		return $apiResult;
	}

	/**
	 * creates lesson
	 * @param Lesson $lesson
	 * @return ApiResult
	 */
	public function createLesson($lesson) {
		// todo: security checks
		if ($lesson->title == "") {
			$apiResult = ApiResultFactory::CreateError("Give it a name", null);
		}
		$this->manager->createLesson($lesson);
		$apiResult = ApiResultFactory::CreateSuccess("Lesson added", $lesson);
		return $apiResult;
	}

	/**
	 * creates a new course
	 * @param Course $course
	 */
	public function createCourse($course) {
		$course->userId = $this->contextUser->userId;
		$this->manager->createCourse($this->contextUser, $course);
	}

	/**
	 * adds content file to course
	 * @param int $courseId
	 * @param string $filename
	 * @param string $uploadFile
	 */
	public function addContentFileToCourse($courseId, $filename, $uploadFile) {
		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		// check contenttype
		$contentType = $this->container['contentRepository']->getContentTypeByExtension($extension);
		if (is_null($contentType)) {
			return ApiResultFactory::CreateError("Select a different file because $extension-files are not allowed", null);
		}
		// check if file needs to be updated
		$exists = $this->container['contentRepository']->getContentObjectByName($courseId, $filename);
		if (is_null($exists)) {
			$contentObject = new ContentObject();
			$contentObject->courseId = $courseId;
			$contentObject->name = $filename;
			$contentObject->md5Hash = md5_file($uploadFile);
			$contentObject->typeId = $contentType->typeId;
			$contentObject->content = '{"file":"'.$filename.'"}';
			$this->container['contentRepository']->createContentObject($contentObject);
			return ApiResultFactory::CreateSuccess("Content created", null);
		} else {
			$exists->md5Hash = md5_file($uploadFile);
			$this->container['contentRepository']->updateMd5Hash($exists);
			return ApiResultFactory::CreateSuccess("Content updated", null);
		}
	}

	/**
	 * retrieves all Courses of an author, regardless of status
	 * @return Array
	 */
	public function getAllAuthorCourses() {
		return $this->manager->getAllAuthorCourses($this->contextUser->userId);
	}



	/**
	 * retrieves all Lessons of a course
	 * @param Course $course
	 * @return Array
	 */
	public function getCourseLessons($course) {

		$cacheHit = $this->serviceCacheManager->cacheFind("lessons", $course->courseId);
		if (!is_null($cacheHit)) {
			return $cacheHit;
		}

		$lessons = $this->manager->getCourseLessons($course);
		$this->serviceCacheManager->updateCache("lessons", $course->courseId, $lessons);
		return $lessons;
	}


	/**
	 * gets Course by name
	 * @param string $name
	 * @param bool $withContentObjects load contentobjects as well
	 * @return Course
	 */
	public function getCourseByName($name, $withContentObjects) {
		$cacheKey = array($name, $withContentObjects);
		$cacheHit = $this->serviceCacheManager->cacheFind("course", $cacheKey);
		if (!is_null($cacheHit)) {
			return $cacheHit;
		}

		$course = $this->manager->getCourseByName($name, $withContentObjects);
		$this->manager->loadCurriculum($course);
		$this->serviceCacheManager->updateCache("course", $cacheKey, $course);


		return $course;
	}

	/**
	 * gets Course by id
	 * @param int $courseId
	 * @param bool $withContentObjects load contentobjects as well
	 * @return Course
	 */
	public function getCourseById($courseId, $withContentObjects) {
		$cacheKey = array($courseId, $withContentObjects);

		// TODO: reactive caching
		//$cacheHit = $this->serviceCacheManager->cacheFind("course", $cacheKey);
		//if (!is_null($cacheHit)) {
		//	return $cacheHit;
		//}


		$course = $this->manager->getCourseById($courseId, $withContentObjects);
		$this->manager->loadCurriculum($course);
		// TODO: reactive caching
		//$this->serviceCacheManager->updateCache("course", $cacheKey, $course);

		return $course;
	}

	/**
	 * gets more courses of a university
	 * @param University $university
	 * @param Course $course
	 * @return array
	 */
	public function getMoreCourses($university, $course) {
		$cacheKey = array($university->name, $course->courseId);
		$cacheHit = $this->serviceCacheManager->cacheFind("more-courses", $cacheKey);
		if (!is_null($cacheHit)) {
			return $cacheHit;
		}

		$courses = $this->manager->getMoreCourses($university, $course);
		$this->serviceCacheManager->updateCache("more-courses", $cacheKey, $courses);
		return $courses;
	}

	/**
	 * gets next lesson of a course
	 * @param int $courseId
	 * @return Lesson
	 */
	public function getNextLesson($courseId) {
		$lesson = $this->manager->getNextLesson($this->contextUser->userId, $courseId);
		return $lesson;
	}


	/**
	 * gets progress of a lesson
	 * @param int $lessonId
	 * @return Progress
	 */
	public function getLessonProgress($lessonId) {
		$progress = $this->manager->getLessonProgress($this->contextUser->userId, $lessonId);
		return $progress;
	}
}

?>