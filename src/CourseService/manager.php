<?php

/**
 * Course Manager
 */
class CourseManager extends BaseManager {

	private $serviceCacheRepository;
	
	function __construct(BasePdoRepository $repository, $settings, $container, BasePdoRepository $serviceCacheRepository) {
		parent::__construct($repository, $settings, $container);
		$this->serviceCacheRepository = $serviceCacheRepository;		
	}
	
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
	 * switches rank of two sections
	 * @param int $sourceId
	 * @param int $targetId
	 * @return ApiResult
	 */
	public function switchSections($sourceId, $targetId) {
		$source = $this->repository->getSectionById($sourceId);
		$target = $this->repository->getSectionById($targetId);

		$rank = $target->rank;
		$target->rank = $source->rank;
		$source->rank = $rank;

		$this->repository->updateSection($source);
		$this->repository->updateSection($target);

		return ApiResultFactory::CreateSuccess("Sections switched", null);
	}

	/**
	 * switches rank of two lessons
	 * @param int $sourceId
	 * @param int $targetId
	 * @return ApiResult
	 */
	public function switchLessons($sourceId, $targetId) {
		$source = $this->repository->getLessonById($sourceId);
		$target = $this->repository->getLessonById($targetId);

		$rank = $target->rank;
		$sectionRank = $target->sectionRank;
		$target->rank = $source->rank;
		$target->sectionRank = $source->sectionRank;
		$source->rank = $rank;
		$source->sectionRank = $sectionRank;

		$this->repository->updateLesson($source);
		$this->repository->updateLesson($target);

		return ApiResultFactory::CreateSuccess("Lessons switched", null);
	}

	/**
	 * changes order of a section inside a course
	 * @param unknown $course
	 * @param unknown $sourceId
	 * @param unknown $targetId
	 */
	public function reorderSection($course, $sourceId, $targetId) {
		$source = $this->repository->getSectionById($sourceId);
		$target = $this->repository->getSectionById($targetId);

		// todo: checks: source and target must not be null + must both be in same course
		$sourceRank = $source->rank;
		$targetRank = $target->rank;

		// reorder all lessons with rank smaller than target
		if ($sourceRank > $targetRank) {
			// move up -> lessons need to be shifted down
			$this->repository->shiftSections($course->courseId, $target->rank, $source->rank, 1);
			$source->rank = $target->rank;
		} else {
			// move down -> lessons need to be shifted up
			$this->repository->shiftSections($course->courseId, $source->rank, $target->rank, -1);
			$source->rank = $target->rank-1;
		}

		// update source lesson with new rank and section
		$this->repository->updateSection($source);
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
				$course->user = $this->container['displayUserRepository']->getUserById($course->userId);
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
		$urls['view'] = $this->settings['application']['base'] . 'courses/' . $course->name;
		$urls['enroll'] = $this->settings['application']['base'] . 'courses/' . $course->courseId . '/enroll';
		$urls['learn'] = $this->settings['application']['base'] . 'courses/' . $course->name . '';
		$urls['toc'] = $this->settings['application']['base'] . 'courses/' . $course->courseId . '/toc';
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
		$parsedown = new Parsedown();
		$course->sections = $this->getCourseSections($course);
		foreach($course->sections as $section) {
			$section->descriptionHtml = $parsedown->text($section->description);
			$section->lessons = $this->getSectionLessons($section);
			foreach($section->lessons as $lesson) {
				$lesson->descriptionHtml = $parsedown->text($lesson->description);
				if (!is_null($lesson->contentObjectId)) {
					$lesson->content = $this->container["contentRepository"]->getContentObjectById($lesson->contentObjectId);
				}
			}
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
	 * @param int $userId
	 * @param int $n
	 * @param bool $withContentObjects
	 * @return array
	 */
	public function getTopNCourses($userId, $n, $withContentObjects) {
		$courses = $this->repository->getTopNCourses($userId, $n);
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
	 * attention: no recursive tree implemented, just level 1 and level 2
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
	 * creates new Lesson
	 * @param Lesson $lesson
	 * @return Lesson
	 */
	public function createLesson($lesson) {
		$section = $this->repository->getSectionById($lesson->sectionId);
		if (is_null($section)) {
			throw new ValidationException('section "' . $lesson->sectionId . '" does not exist');
		}

		$lesson->name = url_slug($lesson->title);
		$exists = $this->repository->getLessonByName($lesson->courseId, $lesson->name);
		if (!is_null($exists)) {
			throw new ValidationException("Choose a different title, this one already exists");
		}

		$lesson = $this->repository->createLesson($lesson);
		$this->repository->increaseCourseNumLessons($section->courseId, 1);
		$this->repository->increaseSectionNumLessons($section->sectionId, 1);

		$lessons = $this->repository->getLessonsBySectionId($lesson->sectionId);
		$sectionRank = count($lessons);
		$this->repository->updateLessonRanks($lesson->lessonId, $sectionRank, $lesson->rank);

		$this->addLessonUrls($lesson);
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
		$course->uuid = getUUID();
		if (is_null($course->imageName) || $course->imageName == '') {
			$course->imageName = $this->settings['course']['default_image_name'];
		}
		$course = $this->repository->createCourse($course);
		return $course;
	}


	/**
	 * updates a section
	 * @param Section $section
	 * @return ApiResult
	 */
	function updateSection($section) {
		$sectionExists = $this->repository->getSectionById($section->sectionId);
		if (is_null($sectionExists)) {
			throw new NotFoundException("Section does not exist");
		}

		// does this name exist yet?
		if ($section->title != $sectionExists->title) {
			$section->name = url_slug($section->title);
			$exists = $this->repository->getSectionByName($sectionExists->courseId, $section->name);
			if (!is_null($exists)) {
				return ApiResultFactory::CreateError("Choose a different title because this one already exists", null);
			}
		} else {
			$section->name = $sectionExists->name;
		}
		$this->repository->updateSection($section);
		$parsedown = new Parsedown();
		$section->descriptionHtml = $parsedown->text($section->description);
		return ApiResultFactory::CreateSuccess("Section updated", $section);
	}



	/**
	 * publishes course
	 * @param int $courseId
	 * @param bool $isPublished
	 */
	public function publishCourse($courseId, $isPublished) {
		$this->repository->publishCourse($courseId, $isPublished);
		$this->serviceCacheRepository->deleteServiceCaches(ModelTypes::Course, $courseId);
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
	 * creates section
	 * @param int $courseId
	 * @param Section $section
	 */
	public function createSection($courseId, $section) {

		$section->courseId = $courseId;
		$section->name = url_slug($section->title);

		$exists = $this->repository->getSectionByName($courseId, $section->name);
		if (!is_null($exists)) {
			throw new ValidationException("Choose a different title, this one already exists");
		}
		$this->repository->createSection($section);
		$this->repository->increaseCourseNumSections($courseId, 1);
		return $section;
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
	 * deletes a section
	 * @param int sectionId
	 * @return ApiResult
	 */
	function deleteSection($sectionId) {
		$section = $this->repository->getSectionById($sectionId);
		if (is_null($section)) {
			throw new NotFoundException("Section does not exist");
		}

		$lessons = $this->repository->getLessonsBySectionId($sectionId);
		if (count($lessons) > 0) {
			return ApiResultFactory::CreateError("Delete all lessons first before you can delete this section", null);
		}

		$this->repository->deleteSection($sectionId);
		$this->repository->increaseCourseNumSections($section->courseId, -1);
		// re-rank all sections
		$rank = 1;
		$sections = $this->repository->getSectionsByCourseId($section->courseId);
		foreach($sections as $section) {
			$section->rank = $rank;
			$this->repository->updateSectionRank($section->sectionId, $rank);
			$rank++;
		}

		return ApiResultFactory::CreateSuccess("Section deleted", null);
	}

	/**
	 * completely deletes a course
	 * @param int courseId
	 */
	function deleteCourse($courseId) {
		$course = $this->repository->getCourseById($courseId);
		if ($course->isPublished) {
			return ApiResultFactory::CreateError('You need to unpublish this course first before you can delete it', null);
		}
		// todo: check if enrollments exist

		$this->repository->beginTransaction();
		$this->repository->deleteAttachmentContentObjects($courseId);
		$this->repository->deleteContentObjects($courseId);
		$this->repository->deleteCourseAttachments($courseId);
		$this->repository->deleteLessons($courseId);
		$this->repository->deleteSections($courseId);
		$this->serviceCacheRepository->deleteServiceCaches(ModelTypes::Course, $courseId);
		$this->repository->deleteCourse($courseId);
		$this->repository->commit();

		// todo: delete files from disc

		$apiResult = ApiResultFactory::CreateSuccess('Course deleted', null);
		return $apiResult;
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
		if (@$zip->open($filename) === TRUE) {
			$zip->extractTo($tempPath);
			$zip->close();
		} else {
			throw new ManagerException('Cannot unzip uploaded file. Make sure that this is a valid zip-file.');
		}

		// read course file
		$courseJsonFilename = 'course.json';
		$filenameCourse = $tempPath . $courseJsonFilename;
		if (!file_exists($filenameCourse)) {
			throw new ManagerException('Zip file does not contain required file "'.$courseJsonFilename.'"');
		}
		$courseJson = file_get_contents($filenameCourse);
		$courseImport = json_decode($courseJson);
		if (is_null($courseImport)) {
			throw new ManagerException('"'.$courseJsonFilename.'" does not contain valid JSON');
		}

		// $courseImport->name = time();

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
			// at the same time those lessons where we need to for content changes later
			$lessonsToCheckForContentChanges = array();
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
									$lessonsToCheckForContentChanges[] = array(
											"lessonModel" => $lesson,
											"lessonImport" => $lessonImport
									);
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
				
			// STEP 6: update content of existing lessons
			foreach($lessonsToCheckForContentChanges as $l) {
				$lesson = $l["lessonModel"];
				$lessonImport = $l["lessonImport"];

				$coImport = $this->parseContentObject($lessonImport->content);
				$co = $this->container['contentRepository']->getContentObjectById($lesson->contentObjectId);

				echo "delta check lesson " . $lesson->name;
				/*
				 checks:
				 1 typeid geändert?
				 -> datensatz und content löschen
				 2 "content" geändert?
				 -> feld updaten, binary löschen und neu kopieren
				 3 title, description immer pauschal updaten
				 4 hash geändert? binary löschen und updaten
				 	
				 checken anhand importlessons-collection
				 ist zwar ineffizient, jede lesson nochmal zu laden und dazu noch das contentobject
				 reicht aber für den moment
				 */
					
				// todo hier gehts dann weiter mit den deltachecks
					

				if ($co->typeId != $coImport->typeId) {

				}
			}
				
			return ApiResultFactory::createSuccess('Course "'.$course->name.'" updated.', $course);
		} else {
			$course = new Course();
			// create content object for promo video
			if (isset($courseImport->video)) {
				if (!is_null($courseImport->video)) {
					$coVideo = $this->parseContentObject($courseImport->video);
					$this->container['contentRepository']->createContentObject($coVideo);

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
							$co = $this->parseContentObject($attachmentImport);
							$this->container['contentRepository']->createContentObject($co);
							$this->container['courseRepository']->createAttachment($l->lessonId, $co->objectId);
							}
							}
							*/
					}
				}
			}
		}
		// done
		// delete temp files
		foreach($filesToBeDeleted as $fileToBeDeleted => $value) {
			unlink($fileToBeDeleted);
		}


		@unlink($filenameCourse);
		@rmdir($tempPath);

		return ApiResultFactory::createSuccess("course created", $course);
	}

	/**
	 * creates lesson in database from import file
	 * @param Course $course
	 * @param object $lessonImport
	 * @return Lesson
	 */
	private function createLessonFromImport($course, $lessonImport, $section, $tempPath, $uploadPath) {

		$co = $this->parseContentObject($lessonImport->content);
		$co->courseId = $course->courseId;
		$this->container['contentRepository']->createContentObject($co);

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

		// content file
		if ($co->typeId == 2) {
			$jsonObject = json_decode($co->content);
				
			if (isset($jsonObject->file)) {
				// import file locally
				copy($tempPath.$jsonObject->file, $uploadPath.$jsonObject->file);
				$hash = md5_file($tempPath.$jsonObject->file);
				unlink($tempPath.$jsonObject->file);
				// save md5hash
				$co->md5Hash = $hash;
				$this->container['contentRepository']->updateMd5Hash($co);
			}
		}

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
		$section = $this->createSection($course->courseId, $section);
		return $section;
	}


	/**
	 * creates content object based on jsonObject from import file
	 * @param Object jsonObject
	 * @return ContentObject
	 */
	private function parseContentObject($jsonObject) {
		$contentType = $this->container['contentRepository']->getContentTypeByName($jsonObject->type);
		if (is_null($contentType)) {
			throw new ManagerException("ContentType $contentType does not exist. Maybe a plugin is missing?");
		}

		$co = new ContentObject();
		$co->typeId = $contentType->typeId;
		$co->content = json_encode($jsonObject->content); // todo: what if this is plain html? then split into two database fields: content + meta

		$co->name = $jsonObject->content->file;
		if (isset($jsonObject->title) && !$this->isNullOrEmpty($jsonObject->title)) {
			$co->title = $jsonObject->title;
		}
		if (isset($jsonObject->description) && !$this->isNullOrEmpty($jsonObject->description)) {
			$co->description = $jsonObject->description;
		}
		return $co;
	}
}

?>