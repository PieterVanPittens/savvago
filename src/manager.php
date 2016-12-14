<?php

/**
 * BaseService
 */
class BaseService {
	/**
	 * ContextUser
	 * @var User
	 */
	protected $contextUser;

	protected $repository;

	/**
	 * @var Container
	 */
	protected $container;

	
	/**
	 *
	 * @var Settings
	 */
	protected $settings;
	
	function __construct($contextUser, $repository, $settings, $container) {
		if (is_null($contextUser)) {
			throw new Exception("contextUser must not be null");
		}
		$this->contextUser = $contextUser;
		$this->repository = $repository;
		$this->container = $container;
		$this->settings = $settings;
	}
	

	private $cacheInfo = array();
	
	/**
	 * checks if cache for this object is available
	 * @param unknown $objectName
	 * @param unknown $objectId
	 */
	function cacheFind($cacheName, $cacheKey) {
		$cacheTag = $this->createCacheTag($cacheName, $cacheKey);
		$deliverFromCache = false;
		$cacheFileExists = false;
		$tag = $this->container['courseRepository']->getServiceCacheByTag($cacheTag);
		$cacheFileName = $this->settings['cache']['service'] . str_replace(":", "_", $cacheTag);
		if (!is_null($tag)) {
			// valid cache
				
			// now check if cache file is available on disc
			$cacheFileExists = file_exists($cacheFileName);
			$deliverFromCache = $cacheFileExists;
		}
		$this->cacheInfo[$cacheTag] = array('cacheFileExists' => $cacheFileExists, 'tagExists' => !is_null($tag), 'cacheFileName' => $cacheFileName);
		if ($deliverFromCache) {
			$s = file_get_contents($cacheFileName);
			$a = unserialize($s);
			return $a;
		}
		return null;
	}
	
	/**
	 * creates the cache tag from cache name and cache key
	 * @param unknown $cacheName
	 * @param unknown $cacheKey
	 */
	private function createCacheTag($cacheName, $cacheKey) {
		if (is_array($cacheKey)) {
			$cacheTag = $cacheName . ':';
			for ($i = 0; $i < count($cacheKey); $i++) {
				if ($i>0) {
					$cacheTag .= '-';
				}
				$cacheTag .= $cacheKey[$i];
			}
		} else {
			$cacheTag = $cacheName . ':' . $cacheKey;
		}
		return $cacheTag;
	}

	/**
	 * updates cache
	 * @param string $cacheName
	 * @param string $cacheKey
	 * @param object $object
	 */
	function updateCache($cacheName, $cacheKey, iCachable $object) {
		$cacheTag = $this->createCacheTag($cacheName, $cacheKey);
		// update cache
		if (!$this->cacheInfo[$cacheTag]['tagExists']) {
			$this->container['courseRepository']->createServiceCache($cacheTag, $object->getModelType(), $object->getId());
		}
		if (!$this->cacheInfo[$cacheTag]['cacheFileExists']) {
			$s = serialize($object);
			file_put_contents($this->cacheInfo[$cacheTag]['cacheFileName'], $s);
		}
	}
	
	
	
	/**
	 * invalidates all caches for an object
	 * i.e. all cache tags that are associated to this object will be deleted
	 * @param object $object
	 */
	function deleteCaches(iCachable $object) {
		$this->container['courseRepository']->deleteServiceCaches($object->getModelType(), $object->getId());
	}
}

class BaseManager {
	/**
	 *
	 * @var PDO Repository
	 */
	protected $repository;

	/**
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 *
	 * @var Container
	 */
	protected $container;

	function __construct($repository, $settings, $container) {
		$this->repository = $repository;
		$this->settings = $settings;
		$this->container = $container;
	}

	/**
	 * checks if parameter is null
	 * @param unknown $parameter
	 * @throws ValidationException
	 */
	function checkParameterForNull($parameter) {
		if (is_null($parameter)) {
			throw new ValidationException("parameter must not be null");
		}
	}
	
	/**
	 * checks if string is null or empty
	 * @param string $object
	 */
	function isNullOrEmpty($string) {
		if (is_null($string)) {
			return true;
		}
		if ($string == "") {
			return true;
		}
		return false;
	}
	
	
}

/**
 * Course Service
 */
class CourseService extends BaseService {

	
	
	/**
	 * imports course from zip file
	 * @param User $user
	 * @param int universityId
	 * @param string filename
	 * @return ApiResult
	 */
	function importCourse($user, $universityId, $filename) {
		$result = $this->container['courseManager']->importCourse($user, $universityId, $filename);
		if ($result->message->type == MessageTypes::Success) {	
			$this->deleteCaches($result->object);
		}
		return $result;
	}
	
	
	
	
	/**
	 * enrolls this user to a course
	 * @param Course $course
	 */
	public function enrollToCourse($course) {
		$this->container['courseManager']->enrollToCourse($this->contextUser, $course);
	}
	
	/**
	 * checks if user is enrolled to course
	 * @param Course $course
	 * @return bool
	 */
	public function isEnrolled($course) {
		return $this->container['courseManager']->isEnrolled($this->contextUser, $course);
	}
	
	/**
	 * gets all courses that a this user is enrolled to
	 * @return array
	 */
	public function getMyCourses() {
		return $this->container['courseManager']->getEnrolledCourses($this->contextUser);
	}
	
	/**
	 * finishes a lesson
	 * @param Lesson $lesson
	 */
	public function finishLesson($lesson) {
		$this->container['courseManager']->finishLesson($this->contextUser, $lesson);
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
		$this->container['courseManager']->loadCourseProgresses($this->contextUser, $course);
	}
	
	/**
	 * creates a new course
	 * @param Course $course
	 */
	public function createCourse($course) {
		$this->container['courseManager']->createCourse($this->contextUser, $course);	
	}
	
	/**
	 * retrieves all Courses of an author, regardless of status
	 * @return Array
	 */
	public function getAllAuthorCourses() {
		return $this->container['courseManager']->getAllAuthorCourses($this->contextUser->userId);
	}
	
	
	
	/**
	 * retrieves all Lessons of a course
	 * @param Course $course
	 * @return Array
	 */
	public function getCourseLessons($course) {
		
		$cacheHit = $this->cacheFind("lessons", $course->courseId);
		if (!is_null($cacheHit)) {
			return $cacheHit;
		}
		
		$lessons = $this->container['courseManager']->getCourseLessons($course);
		$this->updateCache("lessons", $course->courseId, $lessons);
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
		$cacheHit = $this->cacheFind("course", $cacheKey);
		if (!is_null($cacheHit)) {
			return $cacheHit;
		}

		$courseManager = $this->container['courseManager']; 
		
		$course = $courseManager->getCourseByName($name, $withContentObjects);
		$courseManager->loadCurriculum($course);
		$this->updateCache("course", $cacheKey, $course);
		
		
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
		$cacheHit = $this->cacheFind("course", $cacheKey);
		if (!is_null($cacheHit)) {
			return $cacheHit;
		}
	
		$courseManager = $this->container['courseManager'];
	
		$course = $courseManager->getCourseById($courseId, $withContentObjects);
		$courseManager->loadCurriculum($course);
		$this->updateCache("course", $cacheKey, $course);
	
	
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
		$cacheHit = $this->cacheFind("more-courses", $cacheKey);
		if (!is_null($cacheHit)) {
			return $cacheHit;
		}
	
		$courses = $this->container['courseManager']->getMoreCourses($university, $course);
		$this->updateCache("more-courses", $cacheKey, $courses);
		return $courses;
	}

	/**
	 * gets next lesson of a course
	 * @param int $courseId
	 * @return Lesson
	 */
	public function getNextLesson($courseId) {		
		$lesson = $this->container['courseManager']->getNextLesson($this->contextUser->userId, $courseId);
		return $lesson;
	}

	
	/**
	 * gets progress of a lesson
	 * @param int $lessonId
	 * @return Progress
	 */
	public function getLessonProgress($lessonId) {
		$progress = $this->container['courseManager']->getLessonProgress($this->contextUser->userId, $lessonId);
		return $progress;
	}
	
}

/**
 * Course Manager
 */
class CourseManager extends BaseManager {

	
	/**
	 * gets progress of a lesson
	 * @param int $userId
	 * @param int $lessonId
	 * @return Progress
	 */
	public function getLessonProgress($userId, $lessonId) {
		$progress = $this->repository->getProgress($userId, $lessonId, ProgressTypes::FinishedLesson);
		return $progress;
	}
	
	
	/**
	 * changes order of a lesson inside a course
	 * @param unknown $course
	 * @param unknown $sourceId
	 * @param unknown $targetId
	 */
	public function reorderLesson($course, $sourceId, $targetId) {
		$source = $this->repository->getLessonById($sourceId);
		$target = $this->repository->getLessonById($targetId);

		// todo: checks: source and target must not be null + must both be in same course
		$sourceRank = $source->rank;
		$targetRank = $target->rank;
		
		
		// reorder all lessons with rank smaller than target
		if ($sourceRank > $targetRank) {
			// move up -> lessons need to be shifted down
			$this->repository->shiftLessons($course->courseId, $target->rank, $source->rank, 1);
			$source->rank = $target->rank;
		} else {
			// move down -> lessons need to be shifted up
			$this->repository->shiftLessons($course->courseId, $source->rank, $target->rank, -1);
			$source->rank = $target->rank-1;
		}
		
		// update source lesson with new rank and section
		$source->sectionId = $target->sectionId;
		$this->repository->updateLesson($source);
		
		
	}
	
	/**
	 * retrieves all Courses of a university
	 * @param University $university
	 * @param bool $withContentObjects
	 * @return Array
	 */
	public function getUniversityCourses($university, $withContentObjects) {
		$courses = $this->repository->getUniversityCourses($university->universityId);
		$this->prepareCourses($courses, $withContentObjects);
		return $courses;
	}
	
	/**
	 * retrieves all published Courses of an author
	 * @param int $userId
	 * @return Array
	 */
	public function getAuthorCourses($userId) {
		$courses = $this->repository->getAuthorCourses($userId);
		$this->prepareCourses($courses, true);
		return $courses;
	}

	/**
	 * retrieves all Courses of an author, regardless of status
	 * @param int $userId
	 * @return Array
	 */
	public function getAllAuthorCourses($userId) {
		$courses = $this->repository->getAllAuthorCourses($userId);
		$this->prepareCourses($courses, true);
		return $courses;
	}


	
	private function prepareCourses($courses, $withContentObjects) {
		foreach($courses as $course) {
			/*
			if (is_null($course->university) && !is_null($course->universityId)) {
				$course->university = $this->container['universityManager']->getUniversityById($course->universityId);
			}
			if (is_null($course->category) && !is_null($course->categoryId)) {
				$course->category = $this->getCategoryById($course->categoryId);
			}
			*/
			if (is_null($course->user)) {
				$course->user = $this->container['userManager']->getUserById($course->userId); 
			}
			$this->addCourseUrls($course);
			if ($withContentObjects) {
				$course = $this->loadCourseContentObjects($course);
			}
		}
		return $courses;
	}

	/**
	 * generates all urls for a course
	 * @param Course $course
	 */
	private function addCourseUrls($course) {
		/*
		if (!isset($course->university)) {
			throw new ManagerException('university not set');
		}
		*/
		$urls['view'] = $this->settings['application']['base'] . 'courses/' . $course->name;
		$urls['enroll'] = $this->settings['application']['base'] . 'courses/' . $course->courseId . '/enroll';
		$urls['learn'] = $this->settings['application']['base'] . 'courses/' . $course->name . '';
		$urls['teach'] = $this->settings['application']['base'] . 'teach/' . $course->courseId;
		
		foreach($this->settings['course']['image_formats'] as $key => $value) {
			$urls['images'][$key] = $this->settings['application']['base'] . 'upload/' . $course->name .'/'. $value['width'].'x'.$value['height'] .'-'. $course->imageName;
		}
		
		$course->urls = $urls;
	}


	
	/**
	 * generates all urls for a section
	 * @return array
	 */
	private function addSectionUrls($section) {
		if (!isset($section->course)) {
			throw new ManagerException('course not set');
		}
		$urls = array(
			'view' => $this->settings['application']['base'] . 'courses/' . $section->course->name."/" . $section->name
			);
		$section->urls = $urls;
	}

	
	/**
	 * gets next lesson
	 * @param int $userId
	 * @param int $courseId
	 * @return Lesson
	 */
	public function getNextLesson($userId, $courseId) {
		$lesson = $this->repository->getNextLesson($userId, $courseId);		
		$this->addLessonUrls($lesson);
		return $lesson;
	}
	
	
	
	/**
	 * retrieves all Sections of a course
	 * @param Course $course
	 * @return Array
	 */
	public function getCourseSections($course) {
		$models = $this->repository->getCourseSections($course->courseId);
		foreach($models as $section) {
			$section->course = $course;
			$this->addSectionUrls($section);
		}
		return $models;
	}
	
	
	/**
	 * retrieves all Lessons of a course
	 * @param Course $course
	 * @return Array
	 */
	public function getCourseLessons($course) {
		$models = $this->repository->getLessonsByCourseId($course->courseId);
		
		$lineItems = array();
		$curSectionId = 0;
		$rankInTable = 0;
		foreach($models as $lesson) {
			
			if ($curSectionId <> $lesson->sectionId) { // new section
				$section = $this->repository->getSectionById($lesson->sectionId);
				
				$lineItems[] = [
						'title' => $section->title,
						'type' => 'section',
						'id' => $section->sectionId,
						'rank' => $section->rank,
						'rankInTable' => $rankInTable
					
				];
				$rankInTable++;
				
				$curSectionId = $lesson->sectionId;
			}
			$lineItems[] = [
					'title' => $lesson->title,
					'type' => 'lesson',
					'id' => $lesson->lessonId,
					'rank' => $lesson->rank,
					'rankInTable' => $rankInTable
								
			];
			$rankInTable++;			
		}
		return $lineItems;
	}

	
	

	/**
	 * retrieves a topic by name
	 * @param Course $course
	 * @param string $name
	 * @return Section
	 */
	public function getSectionByName($course, $name) {
		if ($name == "") {
			throw new ParameterException("name is empty");
		}
		$model = $this->repository->getSectionByName($course->courseId, $name);
		if ($model == null) {
			throw new NotFoundException($name);
		}
		$model->course = $course;
		return $model;
	}
	
	/**
	 * gets Course by name
	 * @param string $name
	 * @param bool $withContentObjects load contentobjects as well
	 * @return Course 
	 */	
	public function getCourseByName($name, $withContentObjects) {
		if ($name == "") {
			throw new ParameterException("name is empty");
		}
		$course = $this->repository->getCourseByName($name);
		if ($course == null) {
			throw new NotFoundException($name);
		}
		//$course->university = $university;
		$this->addCourseUrls($course);
		if ($withContentObjects) {
			$course = $this->loadCourseContentObjects($course);
		}
		return $course;
	}

	/**
	 * gets Course by id
	 * @param int $id
	 * @param bool $withContentObjects load contentobjects as well
	 * @return Course 
	 */	
	public function getCourseById($courseId, $withContentObjects) {
		$course = $this->repository->getCourseById($courseId);
		if ($course == null) {
			throw new NotFoundException($name);
		}
		$course->university = $this->container['universityRepository']->getUniversityById($course->universityId);
		$this->addCourseUrls($course);
		if ($withContentObjects) {
			$course = $this->loadCourseContentObjects($course);
		}
		return $course;
	}
	
	/**
	 * loads content objects of a course
	 * @param Course $course
	 * @return Course
	 */
	private function loadCourseContentObjects($course) {
		if (!is_null($course->videoId)) {
			$course->video = $this->container['contentRepository']->getContentObjectById($course->videoId);
		}
		return $course;
	}
	
	/**
	 * gets Lessons of a section
	 * @param Section $section
	 * @return array
	 */	
	public function getSectionLessons($section) {
		$models = $this->repository->getLessonsBySectionId($section->sectionId);
		foreach($models as $lesson) {
			$this->addLessonUrls($lesson);
		}
		return $models;
	}

	/**
	 * adds urls to Lesson
	 * @param Lesson $lesson
	 */
	private function addLessonUrls($lesson) {
		$urls = array(
			'view' => $this->settings['application']['base'] . 'lessons/' . $lesson->lessonId ,
			'finish' => $this->settings['application']['api'] . 'lessons/' . $lesson->lessonId . '/finish'
			);
		
		$urls['toc'] = $this->settings['application']['base'] . 'courses/' . $lesson->courseId . '/toc';
		if (is_null($lesson->course)) {
			// todo: cache course
			$lesson->course = $this->repository->getCourseById($lesson->courseId);
			$this->addCourseUrls($lesson->course);
		}
		
		if ($this->isNullOrEmpty($lesson->imageName)) {
			$imageName = $lesson->course->imageName;
		} else {
			$imageName = $lesson->imageName;
		}
		foreach($this->settings['course']['image_formats'] as $key => $value) {
			$urls['images'][$key] = $this->settings['application']['base'] . 'upload/' . $lesson->course->name . '/' . $value['width'].'x'.$value['height'] .'-'. $imageName;
		}
		
		
		$lesson->urls = $urls;
	}
	
	/**
	 * gets curriculum of a course
	 * @param Course $course
	 * @return Course
	 */
	public function loadCurriculum($course) {
		$course->sections = $this->getCourseSections($course);
		foreach($course->sections as $section) {
			$section->lessons = $this->getSectionLessons($section);
		}
		return $course;
	}
	
	/**
	 * gets more courses of a university
	 * @param University $university
	 * @param Course $course
	 * @return array
	 */
	public function getMoreCourses($university, $course) {
		return $this->repository->getTopNUniversityCourses($university->universityId, $course->courseId, 4);
	}
	
	/**
	 * enrolls user to course
	 * @param User $user
	 * @param Course $course
	 */
	public function enrollToCourse($user, $course) {
		$enrollment = $this->repository->getEnrollment($user->userId, $course->courseId);
		if (!is_null($enrollment)) {
			throw new BusinessException(sprintf('You are already enrolled to course %s', $course->title));
		}

		$enrollment = new Enrollment();
		$enrollment->userId = $user->userId;
		$enrollment->courseId = $course->courseId;
		$enrollment->timestamp = time();
		$this->repository->createEnrollment($enrollment);

		$this->repository->increaseCourseNumEnrollments($enrollment->courseId, 1);
		
		// log progress
		$progress = new Progress();
		$progress->userId = $user->userId;
		$progress->referenceId = $course->courseId;
		$progress->courseId = $course->courseId;
		$progress->timestamp = time();
		$progress->type = ProgressTypes::Enrolled;
		$this->repository->createProgress($progress);		
	}
	
	/**
	 * gets top n courses based on num enrollments
	 * @param int $n 
	 * @param bool $withContentObjects 
	 * @return array
	 */
	public function getTopNCourses($n, $withContentObjects) {
		$courses = $this->repository->getTopNCourses($n);
		$this->prepareCourses($courses, $withContentObjects);
		return $courses;
	}
	
	
	/**
	 * gets all courses a user is enrolled to
	 * @param User $user
	 * @return array
	 */
	public function getEnrolledCourses($user) {
		$courses = $this->repository->getEnrolledCourses($user->userId);
		$this->prepareCourses($courses, true);		
		return $courses;
	}
	
	/**
	 * gets tree of Categories
	 * attention: no recursive tree implement, just level 1 and level 2
	 * @return array
	 */
	public function getCategoriesTree() {
		$cats = $this->repository->getCategorys();
		$tree = array();
		foreach($cats as $cat) {
			if (is_null($cat->level2)) {
				
				$urls = array(
				'view' => $this->settings['application']['base'] . 'courses/'.$cat->name
				);
				$cat->urls = $urls;
				$curItem = $cat;
				$tree[] = $curItem;
			
			} else {
				$urls = array(
				'view' => $this->settings['application']['base'] . 'courses/'.$curItem->name . '/' . $cat->name
				);
				$cat->urls = $urls;
				$curItem->categories[] = $cat;
			}
		}
		
		return $tree;
	}
	
	/**
	 * gets category by name
	 * @param string $name
	 * @return Category
	 */
	public function getCategoryByName($name) {
		return $this->repository->getCategoryByName($name);
	}
	
	/**
	 * gets lesson by id
	 * @param int $id
	 * @param bool $withContentObjects
	 * @return Lesson
	 */
	public function getLessonById($id, $withContentObjects) {
		$lesson = $this->repository->getLessonById($id);
		$this->addLessonUrls($lesson);

		if ($withContentObjects) {
			$lesson->content = $this->container['contentRepository']->getContentObjectById($lesson->contentObjectId);
		}
		return $lesson;
	}

	/**
	 * gets section by id
	 * @param int $id
	 * @param bool $withContentObjects
	 * @return Section
	 */
	public function getSectionById($id, $withContentObjects) {
		$section = $this->repository->getSectionById($id);
		return $section;
	}

	
	/**
	 * gets courses of a category
	 * @param Category $category
	 * @param bool $withContentObjects
	 * @return array
	 */
	public function getCategoryCourses($category, $withContentObjects) {
		$courses = $this->repository->getCategoryCourses($category->categoryId);
		$this->prepareCourses($courses, $withContentObjects);
		return $courses;
	}
	
	
	/**
	 * creates new topic
	 * @param Section $topic
	 * @return Section
	 */
	public function createSection($topic) {
		$topic = $this->repository->createSection($topic);
		$this->repository->increaseCourseNumSections($topic->courseId, 1);
		return $topic;
	}
	
	/**
	 * creates new Lesson
	 * @param Lesson $lesson
	 * @return Lesson
	 */
	public function createLesson($lesson) {
		$section = $this->repository->getSectionById($lesson->sectionId);
		if (is_null($section)) {
			throw new ManagerException('section "' . $lesson->sectionId . '" does not exist');
		}
		$lesson = $this->repository->createLesson($lesson);
		$this->repository->increaseCourseNumLessons($section->courseId, 1);
		$this->repository->increaseSectionNumLessons($section->sectionId, 1);
		
		$lessons = $this->repository->getLessonsBySectionId($lesson->sectionId);
		$sectionRank = count($lessons);
		$this->repository->updateLessonRanks($lesson->lessonId, $sectionRank, $lesson->rank);
		
		return $lesson;
	}

	
	/**
	 * checks if user is enrolled to course
	 * @param User $user
	 * @param Course $course
	 * @return bool
	 */
	public function isEnrolled($user, $course) {
		$enrollment = $this->repository->getEnrollment($user->userId, $course->courseId);
		return !(is_null($enrollment));
	}
	
	/**
	 * creates new Course
	 * @param User $user
	 * @param Course $course
	 * @return Course
	 */
	public function createCourse($user, $course) {
		$course->userId = $user->userId;
		if (is_null($course->imageName) || $course->imageName == '') {
			$course->imageName = $this->settings['course']['default_image_name'];
		}
		$course = $this->repository->createCourse($course);
		return $course;
	}

	/**
	 * publishes course
	 * @param Course $course
	 * @param bool $isPublished
	 */
	public function publishCourse($course, $isPublished) {
		$this->repository->publishCourse($course->courseId, $isPublished);
		$this->repository->deleteServiceCaches(ModelTypes::Course, $course->courseId);
		return $course;
	}
	
	
	/**
	 * finishes a learningobject
	 * @param User $user
	 * @param Lesson $lesson
	 */
	public function finishLesson($user, $lesson) {
		$section = $this->repository->getSectionById($lesson->sectionId);
	
		// check: is user enrolled to course?
		$enrollment = $this->repository->getEnrollment($user->userId, $lesson->courseId);
		if (is_null($enrollment)) {
			throw new ValidationException("you are not enrolled to this course");
		}
		
		// check: has user already finished this one?
		$progress = $this->repository->getProgress($user->userId, $lesson->lessonId, ProgressTypes::FinishedLesson);
		if (!is_null($progress)) {
			throw new ValidationException("you already finished this lesson");
		}
		
		
		// log finished the lesson
		$progress = new Progress();
		$progress->userId = $user->userId;
		$progress->referenceId = $lesson->lessonId;
		$progress->courseId = $section->courseId;
		$progress->timestamp = time();
		$progress->type = ProgressTypes::FinishedLesson;
		$this->repository->createProgress($progress);
		
		// update progress in section
		$sectionProgress = $this->repository->getProgress($user->userId, $lesson->sectionId, ProgressTypes::SectionFinishedLessonsTotal);
		if (is_null($sectionProgress)) {
			$sectionProgress = new Progress();
			$sectionProgress->userId = $user->userId;
			$sectionProgress->referenceId = $section->sectionId;
			$sectionProgress->courseId = $section->courseId;
			$sectionProgress->type = ProgressTypes::SectionFinishedLessonsTotal;
			$sectionProgress->timestamp = time();
			$sectionProgress->value = 1;
			$this->repository->createProgress($sectionProgress);
		} else {
			$sectionProgress->timestamp = time();
			$sectionProgress->value++;
			$this->repository->updateProgress($sectionProgress);
		}
		
		// update progress in course
		$courseProgress = $this->repository->getProgress($user->userId, $section->courseId, ProgressTypes::CourseFinishedLessonsTotal);
		if (is_null($courseProgress)) {
			$courseProgress = new Progress();
			$courseProgress->userId = $user->userId;
			$courseProgress->referenceId = $section->courseId;
			$courseProgress->courseId = $section->courseId;
			$courseProgress->type = ProgressTypes::CourseFinishedLessonsTotal;
			$courseProgress->timestamp = time();
			$courseProgress->value = 1;
			$numRows = $this->repository->createProgress($courseProgress);
		} else {
			$courseProgress->timestamp = time();
			$courseProgress->value++;
			$this->repository->updateProgress($courseProgress);
		}
	}
	





	
	
	/**
	 * gets category by id
	 * @param int $categoryId
	 * @return Category
	 */
	public function getCategoryById($categoryId) {
		$category = $this->repository->getCategoryById($categoryId);
		if (!is_null($category)) {
			$this->addCategoryUrls($category);
		}
		return $category;
	}

	/**
	 * generates urls for a Category
	 * @param Category $category
	 */
	private function addCategoryUrls($category) {
		$urls = array(
			'view' => $this->settings['application']['base'] . 'courses/category/'.$category->name
			);
		$category->urls = $urls;
	}
	
	
	/**
	 * gets all progress of a user
	 * @param User $user
	 * @param Course $course
	 * @return array
	 */
	public function loadCourseProgresses($user, $course) {
		// get all progress items for course
		$ps = $this->repository->getCourseProgresses($user->userId, $course->courseId);
		// filter to course level
		$psCourse = array_filter(
			$ps,
			function ($e) {
				return $e->type == ProgressTypes::CourseFinishedLessonsTotal
				|| $e->type == ProgressTypes::Enrolled
				;
			}
		);
		// convert to hash map
		$progresses = array();
		foreach($psCourse as $progress) {
			$progresses[$progress->type] = $progress;
		}
		$course->progresses = $progresses;
		// now distribute progress on all subitems
		foreach($course->sections as $section) {
			// filter to section level
			$psSection = array_filter(
			$ps,
				function ($e) use ($section) {
					return ($e->type == ProgressTypes::SectionFinishedLessonsTotal) && ($e->referenceId == $section->sectionId);
				}
			);
			// convert to hash map
			$progresses = array();
			foreach($psSection as $progress) {
				$progresses[$progress->type] = $progress;
			}
			$section->progresses = $progresses;
			if (!isset($section->progresses[ProgressTypes::SectionFinishedLessonsTotal])) {
				$p = new Progress();
				$p->value = 0;
				$section->progresses[ProgressTypes::SectionFinishedLessonsTotal] = $p;
			}

			$nextLessonId = null;
			foreach($section->lessons as $lesson) {
				// filter to lesson level
				$psLesson = array_filter(
				$ps,
					function ($e) use ($lesson) {
						return ($e->type == ProgressTypes::FinishedLesson) && ($e->referenceId == $lesson->lessonId);
					}
				);
				// convert to hash map
				$progresses = array();
				foreach($psLesson as $progress) {
					$progresses[$progress->type] = $progress;
				}
				$lesson->progresses = $progresses;
				if (is_null($nextLessonId) && count($psLesson) ==0) {
					$nextLessonId = $lesson->lessonId;
				}
			}
			$section->urls['nextLesson'] = $this->settings['application']['base'] . 'lessons/' . $nextLessonId;
		}
	}
	
	/**
	 * converts curriculum to string for quickedit
	 * @param Course $course
	 * @return Course
	 */
	static function getCurriculumAsString($course) {
		$str = '';
		foreach($course->sections as $section) {
			$str .= $section->title."\n";
			foreach($section->lessons as $lesson) {
				$str .= '*'.$lesson->title."\n";
			}
		}
		return $str;
	}
	
	
	
	/**
	 * creates sections and lessons from string
	 * @param Course $course
	 * @param string $curriculum
	 */
	public function quickCreateCurriculum($course, $curriculum) {
	
		// delete existing curriculum first
		$this->repository->deleteCurriculum($course->courseId);

	
		$lines = explode("\n", $curriculum);
		$sections = array();
		$curSection = null;
		foreach($lines as $line) {
			if ($line == '') {
				continue;
			}
			if ($line[0] === '*') { // lesson
				if (is_null($curSection)) {
					throw new ManagerException("first line needs to be a section, not a lesson");
				}
				$lesson = new Lesson();
				$lesson->setTitle(substr($line,1));
				$lesson->sectionId = $curSection->sectionId;
				$lesson->courseId = $course->courseId;
				$curSection->lessons[] = $lesson;
				$this->repository->createLesson($lesson);
			} else { // section
				$curSection = new Section();
				$curSection->setTitle($line);
				$curSection->courseId = $course->courseId;
				$this->repository->createSection($curSection);
				$course->sections[] = $curSection;
			}
		}
	}
	
	/**
	 * completely deletes a course
	 * @param Course course
	 */
	function deleteCourse($course) {
		$this->repository->deleteAttachmentContentObjects($course->courseId);
		$this->repository->deleteCourseAttachments($course->courseId);
		$this->repository->deleteLessons($course->courseId);
		$this->repository->deleteSections($course->courseId);
		$this->repository->deleteServiceCaches(ModelTypes::Course, $course->courseId);
		$this->repository->deleteCourse($course->courseId);
	
		// todo: delete files from disc
	
	}
	
	/**
	 * imports course from zip file
	 * @param User $user
	 * @param int universityId
	 * @param string filename
	 * @return Course
	 */
	function importCourse($user, $universityId, $filename) {

		// check folders
		if (!file_exists($this->settings['upload']['upload_path'])) {
			return ApiResultFactory::createError('upload_path does not exist', null);
		}

		
		// extract zip first
		$zip = new ZipArchive;
		$guid = com_create_guid();
		$tempPath = $this->settings['import']['import_path'] . $guid . '/';
		if ($zip->open($filename) === TRUE) {
			$zip->extractTo($tempPath);
			$zip->close();
		} else {
			throw new ManagerException('Could not unzip course file: '.$filename);
		}

		// read course file
		$filenameCourse = $tempPath . 'course.json';
		$courseJson = file_get_contents($filenameCourse);
		$courseImport = json_decode($courseJson);
		if (is_null($courseImport)) {
			throw new ManagerException("$filenameCourse does not contain valid JSON");
		}

		$uploadPath = $this->settings['upload']['upload_path'] . $courseImport->name . '/';

		// check if course exists
		$course = $this->repository->getCourseByName($courseImport->name);

		// course exists, check if it can be updated
		$isDeltaUpdate = false;
		if ($course != null) {
		
			// cannot be updated if published
			if ($course->isPublished) {
				return ApiResultFactory::createError('Course "' . $course->name. '" is published. Unpublish the course first if you want to update it.', null);
			}
			
			$hasEnrollments = $course->numEnrollments > 0;
			$numProgresses = $this->repository->getNumProgresses($course->id);
			$hasProgresses = $numProgresses > 0;
			
			if (!$hasEnrollments && !$hasProgresses) {
				// delete course completely
				$this->deleteCourse($course);
			} else {
				// course must not be deleted
				// delta update required
				$isDeltaUpdate = true;
				// create course
			}
		}

		
		
		
		// course
		if ($isDeltaUpdate) {
			
			// determine lessons to be deleted
			$lessonsToBeDeleted = array();
			$lessons = $this->repository->getLessonsByCourseId($course->courseId);
			foreach($lessons as $lesson) {
				$lessonFound = false;
				if (isset($courseImport->sections)) {
					foreach ($courseImport->sections as $sectionImport) {
						if (isset($sectionImport->lessons)) {
							foreach ($sectionImport->lessons as $lessonImport) {
								if (isset($lessonImport->name)) {
									$name = $lessonImport->name;
								} else {
									$name = url_slug($lessonImport->title);
								}
								$lessonImport->name = $name;
								if ($name == $lesson->name) {
									$lessonFound = true;
								}
							}
						}
					}
				}
				if (!$lessonFound) {
					$lessonsToBeDeleted[] = $lesson;
				}
			}
			// determine sections to be deleted
			// section must be empty to be deleted, otherwise it would imply deleting lessons
			// if no lessonstobedeleted we can assume that sections can safely be deleted
			// -> obviously the lessons had been moved to other sections because there are not lessonstobedeleted

			
			// if there are enrollments already: nothing may be deleted
			if ($hasEnrollments && count($lessonsToBeDeleted) > 0) {
				$lessonNames = array();
				foreach($lessonsToBeDeleted as $lesson) {
					$lessonNames[] = $lesson->name;
				}
				return ApiResultFactory::createError('Course already has enrolled students. It is not allowed to reduce the scope of the course anymore. These lessons need to remain in the course:', $lessonNames);
			}


			/*
						reihenfolge:
			1. sections adden (rank egal) -> done
			2. lessons adden (rank egal) -> done
			3. lessons umhängen und ranks updaten -> done
			4. sections löschen -> done
			5. contents updaten
			pauschal jeden content updaten? oder kann man ein delta erkennen?
			erkennen dürfte sinnvoller sein
			also metadaten speichern, am besten ein hash vom content
			*/

			// STEP 1: add new sections
			// determine sections to be added
			$sectionsToBeAdded = array();
			$sections = $this->repository->getSectionsByCourseId($course->courseId);
			if (isset($courseImport->sections)) {
				foreach ($courseImport->sections as $sectionImport) {
					$sectionFound = false;
					if (isset($sectionImport->name)) {
						$name = $sectionImport->name;
					} else {
						$name = url_slug($sectionImport->title);
					}
					$sectionImport->name = $name;
					foreach($sections as $section) {
						if ($section->name == $name) {
							$sectionFound = true;
						}
					}
					if (!$sectionFound) {
						$sectionsToBeAdded[] = $sectionImport;
					}
				}
			}
			
			// add new sections
			foreach($sectionsToBeAdded as $sectionImport) {
				$section = $this->createSectionFromImport($course, $sectionImport);
			}

			// STEP 2: add new lessons
			// determine lessons to be added
			$lessons = $this->repository->getLessonsByCourseId($course->courseId);
			if (isset($courseImport->sections)) {
				foreach ($courseImport->sections as $sectionImport) {
					if (isset($sectionImport->lessons)) {
						foreach ($sectionImport->lessons as $lessonImport) {
							$lessonFound = false;
							if (isset($lessonImport->name)) {
								$name = $lessonImport->name;
							} else {
								$name = url_slug($lessonImport->title);
							}
							foreach($lessons as $lesson) {
								if ($lesson->name == $name) {
									$lessonFound = true;
								}
							}
							if (!$lessonFound) {
								$section = $this->repository->getSectionByName($course->courseId, $sectionImport->name);
								if (is_null($section)) {
									throw new ManagerException('section "'.$sectionImport->name.'" does not exist');
								}
								// add lesson
								$l = $this->createLessonFromImport($course, $lessonImport, $section, $tempPath, $uploadPath);
							}
						}
					}
				}
			}
			// STEP 3: update ranks
			if (isset($courseImport->sections)) {
				$sectionRank = 0;
				foreach ($courseImport->sections as $sectionImport) {
					$numLessons = 0;
					$section = $this->repository->getSectionByName($course->courseId, $sectionImport->name);
					if (is_null($section)) {
						throw new ManagerException('section "'.$setionImport->name.'" does not exist');
					}
					$this->repository->updateSectionRank($section->sectionId, $sectionRank);
					if (isset($sectionImport->lessons)) {
						$lessonRank = 0;
						foreach ($sectionImport->lessons as $lessonImport) {
							$numLessons++;
							$lesson = $this->repository->getLessonByName($course->courseId, $lessonImport->name);
							$lesson->sectionRank = $sectionRank;
							$lesson->rank = $lessonRank;
							$lesson->sectionId = $section->sectionId;
							$this->repository->updateLesson($lesson);
							$lessonRank++;
						}
					}
					$this->repository->updateSectionNumLessons($section->sectionId, $numLessons);
					$sectionRank++;
				}
			}
			// STEP 4: delete lessons
				// this case does not exist for delta update:
				// deletion is allowed only if no enrollments and progress yet
				// and that means the whole course will be deleted anyway
				
			// STEP 5: delete sections
			// by definition now only empty sections should be left for deletion
			$sectionsToBeDeleted = array();
			foreach($sections as $section) {
				$found = false;
				if (isset($courseImport->sections)) {
					foreach ($courseImport->sections as $sectionImport) {
						if ($section->name == $sectionImport->name) {
							$found = true;
						}
					}
				}
				if (!$found) {
					// section not in file anymore, so needs to be deleted
					$s = $this->repository->getSectionById($section->sectionId);
					if ($s->numLessons == 0) {
						$this->repository->deleteSection($section->sectionId);
					} else {
						throw ManagerException('Section "'.$section->name.'" cannot be deleted because it still contains lessons. Remove these lessons first.');
					}
				}
			}
			
			return ApiResultFactory::createSuccess('Course "'.$course->name.'" updated.', $course);
		} else {
			$course = new Course();
			// create content object for promo video
			if (isset($courseImport->video)) {
				if (!is_null($courseImport->video)) {
					$coVideo = $this->createContentObject($courseImport->video);
					$course->videoId = $coVideo->objectId;
				}
			}
		}
		$course->categoryId = 0;
		/* descoped for the moment
		if (isset($courseImport->category)) {
			$category = $this->repository->getCategoryByName($courseImport->category);
			if (is_null($category)) {
				echo 'category invalid: ' . $courseImport->category;
			} else {
				$course->categoryId = $category->categoryId;
			}
		}		
		*/
		if (isset($courseImport->name)) {
			$course->name = $courseImport->name;
		} else {
			$course->name = url_slug($courseImport->title);
		}
		
		$course->universityId = $universityId;
		$course->title = $courseImport->title;
		$course->imageName = $courseImport->imageName;
		$course->subtitle = $courseImport->subtitle;
		$course->description = $courseImport->description;

		// upload content files
		if (!file_exists($uploadPath)) {
			mkdir($uploadPath);
		}
		$filesToBeDeleted = array();
		
		
		// import image
		$imageFile = $tempPath . $courseImport->imageName;
		$filesToBeDeleted[$imageFile] = '';
		$path_parts = pathinfo($imageFile);
		$newImageName = com_create_guid().'.'.$path_parts['extension'];
		$course->imageName = $newImageName;		
		foreach($this->settings['course']['image_formats'] as $key => $value) {
			$targetFile = $uploadPath . $value['width'].'x'.$value['height'].'-'.$newImageName;
			ImageManager::resizeImage($imageFile, $targetFile, $value['width'], $value['height']);
		}

		$course = $this->createCourse($user, $course);
		
		// create sections
		if (isset($courseImport->sections)) {
			foreach ($courseImport->sections as $sectionImport) {
				$section = $this->createSectionFromImport($course, $sectionImport);
				
				// add lessons
				if (isset($sectionImport->lessons)) {
					foreach ($sectionImport->lessons as $lessonImport) {

						$l = $this->createLessonFromImport($course, $lessonImport, $section, $tempPath, $uploadPath);
						/*
						// attachments
						if (isset($lessonImport->attachments)) {
							foreach($lessonImport->attachments as $attachmentImport) {
								$co = $this->createContentObject($attachmentImport);
								$this->container['courseRepository']->createAttachment($l->lessonId, $co->objectId);
							}
						}
						*/
						// content file
						$co = $l->content;
						if ($co->typeId == 2) {
							$jsonObject = json_decode($co->content);
							var_dump($jsonObject);
							
							if (isset($jsonObject->file)) {
								// import file locally
								copy($tempPath.$jsonObject->file, $uploadPath.$jsonObject->file);
								$filesToBeDeleted[$tempPath.$jsonObject->file] = '';
								// save md5hash
								$hash = md5_file($uploadPath.$jsonObject->file);
								dadadadasdasdasdasd
								hier weitermachen: den md5 hash im co updaten
								und dann kann beim delta import die  neue datei gehashed und hiermit verglichen werden
								und damit ist der deltaupload dann endlich fertig
							}
						}
					}
				}
			}
		}
		// done
		// delete temp files
		foreach($filesToBeDeleted as $fileToBeDeleted => $value) {
			unlink($fileToBeDeleted);
		}
		
		
		unlink($filenameCourse);
		rmdir($tempPath);
		
		return ApiResultFactory::createSuccess("course created", $course);
	}

	/**
	 * creates lesson in database from import file
	 * @param Course $course
	 * @param object $lessonImport
	 * @return Lesson
	 */
	private function createLessonFromImport($course, $lessonImport, $section, $tempPath, $uploadPath) {

		$co = $this->createContentObject($lessonImport->content);
	
		$l = new Lesson();
		$l->sectionId = $section->sectionId;
		
		if (isset($lessonImport->name)) {
			$l->name = $lessonImport->name;
		} else {
			$l->name = url_slug($lessonImport->title);
		}
		if (isset($lessonImport->imageName) && !$this->isNullOrEmpty($lessonImport->imageName)) {
			// import image
			$imageFile = $tempPath . $lessonImport->imageName;
			$filesToBeDeleted[$imageFile] = '';
			$path_parts = pathinfo($imageFile);
			$newImageName = com_create_guid().'.'.$path_parts['extension'];
			$l->imageName = $newImageName;
			foreach($this->settings['course']['image_formats'] as $key => $value) {
				$targetFile = $uploadPath . $value['width'].'x'.$value['height'].'-'.$newImageName;
				ImageManager::resizeImage($imageFile, $targetFile, $value['width'], $value['height']);
			}
		}
		
		$l->title = $lessonImport->title;
		$l->description = $lessonImport->description;
		$l->contentObjectId = $co->objectId;
		$l->courseId = $course->courseId;
		$l->content = $co;
		$l = $this->createLesson($l);
		return $l;
	}
	
	/**
	 * creates section in database from import file
	 * @param Course $course
	 * @param object $sectionImport
	 * @return Section
	 */
	private function createSectionFromImport($course, $sectionImport) {
		$section = new Section();
		$section->courseId = $course->courseId;
		if (isset($sectionImport->name)) {
			$section->name = $sectionImport->name;
		} else {
			$section->name = url_slug($sectionImport->title);
		}
		$section->title = $sectionImport->title;
		$section->description = $sectionImport->description;
		$section = $this->createSection($section);
		return $section;
	}

	
	/**
	 * creates content object based on jsonObject from import file
	 * @param Object jsonObject
	 * @return ContentObject
	 */
	private function createContentObject($jsonObject) {
		$contentType = $this->container['contentRepository']->getContentTypeByName($jsonObject->type);
		if (is_null($contentType)) {
			throw new ManagerException("ContentType $contentType does not exist. Maybe a plugin is missing?");
		}
	
		$co = new ContentObject();
		$co->typeId = $contentType->typeId;
		$co->content = json_encode($jsonObject->content); // todo: what if this is plain html? then split into two database fields: content + meta
		
		if (isset($jsonObject->title) && !$this->isNullOrEmpty($jsonObject->title)) {
			$co->title = $jsonObject->title;
		}
		if (isset($jsonObject->description) && !$this->isNullOrEmpty($jsonObject->description)) {
			$co->description = $jsonObject->description;
		}
		$this->container['contentRepository']->createContentObject($co);
		return $co;
	}
}


?>