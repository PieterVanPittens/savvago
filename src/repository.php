<?php

/**
 * PDO Repository Implementation
 *
 */
class BasePdoRepository {



	/**
	 * object caches
	 */
	protected $objectCaches = array();

	public function cacheObject(iModel $object) {
		if (is_null($object)) {
			throw new RepositoryException('object must not be null');
		}
		//  cache exists?
		$cacheName = get_class($object);
		if (isset($this->objectCaches[$cacheName])) {
			$cache = $this->objectCaches[$cacheName];
		} else {
			$cache = array();
			$this->objectCaches[$cacheName] = $cache;
		}
		// object already cached?
		$filtered = array_filter(
			$cache,
			function ($e) use($object) {
				return $e->getId() == $object->getId();
			}
		);
		// no, so let's cache
		if (count($filtered) == 0) {
			$this->objectCaches[$cacheName][] = $object;
		}
	}

	/**
	 * gets object from cache by id
	 * only objectid needs to be set
	 * @param iModel $object
	 * @return iModel
	 */
	public function getFromCacheById(iModel $object) {
		if (is_null($object)) {
			throw new RepositoryException('object must not be null');
		}
		//  cache exists?
		$cacheName = get_class($object);
		if (!isset($this->objectCaches[$cacheName])) {
			return null;
		}
		$cache = $this->objectCaches[$cacheName];
		// object cached?
		$filtered = array_filter(
			$cache,
			function ($e) use($object) {
				return $e->getId() == $object->getId();
			}
		);
		if (count($filtered) == 1) {
			return array_pop($filtered);
		} else {
			return null;
		}
	}

	public $pdo;

	function __construct($host, $database, $user, $pass) {
	
	    $pdo = new PDO("mysql:host=" . $host . ";dbname=" . $database,
        $user, $pass);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	
		$this->pdo = $pdo;
	}


	protected function prepare($query) {
		try {
			$stmt = $this->pdo->prepare($query);
		} catch (PDOException $e) {
			throw new RepositoryException($stmt->queryString, $e->getMessage());
		}
		return $stmt;
	}
	
	protected function execute($statement, $parameters) {
		try {
			if (count($parameters) > 0 ) {
				$statement->execute($parameters);
			} else {
				$statement->execute();
			}
			return $statement;
		} catch (PDOException $e) {
			var_dump($e->getMessage());
			var_dump($statement->queryString);
			var_dump($parameters);
			die();
			throw new RepositoryException($e->getMessage(), 0, $e );
		}
	}
}

class CourseRepository extends BasePdoRepository {	
	
	private $courseFieldNames = 'name, university_id, title, description, subtitle, image_name, video_id, category_id, num_sections, num_lessons, user_id, num_enrollments, is_published';
	
	
	/**
	 * gets cache entry by objecttag
	 * @param string $tag
	 */
	public function getServiceCacheByTag($tag) {
		$query = "SELECT 1 FROM service_cache where tag = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($tag));
		if ($a = $stmt->fetch()) {
			return '';
		} else {
			return null;
		}
	}
	
	/**
	 * creates service cache entry
	 * @param string $tag
	 * @param ModelTypes $modelType
	 * @param int $modelId
	 */
	public function createServiceCache($tag, $modelType, $modelId) {
		$query = "INSERT INTO service_cache (tag, model_type, model_id) VALUES (?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($tag, $modelType, $modelId);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * deletes all service caches associated with an object
	 * @param ModelTypes $modelType
	 * @param int $modelId
	 */
	public function deleteServiceCaches($modelType, $modelId) {
		// content
		$query = "
		delete from service_cache where model_type = ? and model_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
				$modelType, $modelId
		);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * deletes all lessons of a course
	 * @param int $courseId
	 */
	public function deleteLessons($courseId) {
		$query = "DELETE from lessons where course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));
	}

	/**
	 * deletes all sections of a course
	 * @param int $courseId
	 */
	public function deleteSections($courseId) {
		$query = "DELETE from sections where course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));
	}

	/**
	 * deletes a course
	 * @param int $courseId
	 */
	public function deleteCourse($courseId) {
		$query = "DELETE from courses where course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));
	}
	
	/**
	 * deletes all attachments of a course
	 * @param int $courseId
	 */
	public function deleteCourseAttachments($courseId) {
		// mysql does not support this one:
		// delete att from attachments as att where att.attachment_id in (
		// SELECT attachment_id FROM attachments a, lessons l where a.lesson_id = l.lesson_id and l.course_id = 13
		// )
		
		
		// so read all attachments first, then delete them
		// not perfect but works
    	$query = "SELECT attachment_id from attachments a, lessons l where a.lesson_id = l.lesson_id and l.course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));
		$attachmentIds = array();
		while ($a = $stmt->fetch()) {
			$attachmentIds[] = $a[0];
		}
		foreach($attachmentIds as $attachmentId) {
			$query = "DELETE from attachments where attachment_id = ?";
			$stmt = $this->prepare($query);
			$stmt = $this->execute($stmt, array($attachmentId));
		}
	}
	
	public function deleteAttachmentContentObjects($courseId) {
    	$query = "select object_id from content_objects o, attachments a, lessons l where l.lesson_id = a.lesson_id and a.content_id = o.object_id and l.course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));
		$objectIds = array();
		while ($a = $stmt->fetch()) {
			$objectIds[] = $a[0];
		}
		foreach($objectIds as $objectId) {
			$query = "DELETE from content_objects where object_id = ?";
			$stmt = $this->prepare($query);
			$stmt = $this->execute($stmt, array($objectId));
		}
	}
	
	
	/**
	 * creates Course 
	 * @param Course $model
	 * @return Course 
	 */	
	public function createCourse($model) {
		$query = "INSERT INTO courses (" . $this->courseFieldNames . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->name
			, $model->universityId
			, $model->title
			, $model->description
			, $model->subtitle
			, $model->imageName
			, $model->videoId
			, $model->categoryId
			, 0
			, 0
			, $model->userId
			, 0
			, false
			);
		$stmt = $this->execute($stmt, $parameters);
		$model->courseId = $this->pdo->lastInsertId();
		return $model;
	}
	
	/**
	 * retrieves all Courses
	 * @param int $universityId
	 * @return Array
	 */
	public function getUniversityCourses($universityId) {
		$query = "SELECT course_id, ".$this->courseFieldNames." FROM courses WHERE university_id = ? and is_published = 1";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($universityId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Course::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * retrieves all Courses
	 * @param int $userId
	 * @return Array
	 */
	public function getAuthorCourses($userId) {
		$query = "SELECT course_id, ".$this->courseFieldNames." FROM courses WHERE user_id = ? and is_published = 1";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($userId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Course::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * retrieves all Courses
	 * @param int $userId
	 * @return Array
	 */
	public function getAllAuthorCourses($userId) {
		$query = "SELECT course_id, ".$this->courseFieldNames." FROM courses WHERE user_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($userId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Course::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * retrieves all Courses of a category
	 * @param int $categoryId
	 * @return Array
	 */
	public function getCategoryCourses($categoryId) {
		$query = "SELECT c.name as name, university_id, c.title as title, description, subtitle, image_name, video_id, c.category_id as category_id, num_sections, c.user_id as user_id, num_enrollments, is_published FROM courses c, categories cat where c.category_id = cat.category_id and cat.parent_id = ? and c.is_published = 1";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($categoryId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Course::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * retrieves top n Courses
	 * @param int $n
	 * @return Array
	 */
	public function getTopNCourses($n) {
		$query = 'SELECT course_id, '.$this->courseFieldNames .' FROM courses WHERE is_published = 1 order by num_enrollments DESC Limit '.$n;
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($n));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Course::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * retrieves all Courses a user is enrolled to
	 * @param int $userId
	 * @return Array
	 */
	public function getEnrolledCourses($userId) {
		$query = 'SELECT
		c.course_id as ccourse_id
		, c.name as cname
		, c.title as ctitle
		, c.description as cdescription
		, c.subtitle as csubtitle
		, c.image_name as cimage_name
		, c.video_id as cvideo_id
		, c.category_id as ccategory_id
		, c.num_sections as cnum_sections
		, c.num_lessons as cnum_lessons
		, c.user_id as cuser_id
		, c.num_enrollments as cnum_enrollments
		, c.is_published as cis_published
		, u.university_id as uuniversity_id
		, u.name as uname
		, u.title as utitle
		FROM courses c, enrollments e, universities u WHERE u.university_id = c.university_id and c.course_id = e.course_id and e.user_id = ?  and c.is_published = 1';
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($userId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$course = new Course();
			$course->courseId = $a['ccourse_id'];
			$course->name = $a['cname'];
			$course->title = $a['ctitle'];
			$course->description = $a['cdescription'];
			$course->subtitle = $a['csubtitle'];
			$course->imageName = $a['cimage_name'];
			$course->videoId = $a['cvideo_id'];
			$course->categoryId = $a['ccategory_id'];
			$course->numSections = $a['cnum_sections'];
			$course->numLessons = $a['cnum_lessons'];
			$course->userId = $a['cuser_id'];
			$course->numEnrollments = $a['cnum_enrollments'];
			$course->isPublished = $a['cis_published'];
			$university = new University();
			$university->universityId = $a['uuniversity_id'];
			$university->name = $a['uname'];
			$university->title = $a['utitle'];
			$course->university = $university;

			$models[] = $course;
		}
		return $models;
	}
	
	/**
	 * retrieves all Top N Courses of a university
	 * @param int $universityId
	 * @param int $courseId
	 * @return Array
	 */
	public function getTopNUniversityCourses($universityId, $courseId, $n) {
		$query = "SELECT course_id, ".$this->courseFieldNames." FROM courses WHERE university_id = ? and course_id <> ? and is_published = 1 LIMIT $n";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($universityId, $courseId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Course::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * gets Course by name
	 * @param string $name
	 * @return Course 
	 */	
	public function getCourseByName($name) {
		$query = "SELECT course_id, ".$this->courseFieldNames." FROM courses where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));

		if ($a = $stmt->fetch()) {
			$model = Course::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * gets Course by id
	 * @param int $courseId
	 * @return Course 
	 */	
	public function getCourseById($courseId) {
		$query = "SELECT course_id, ".$this->courseFieldNames." FROM courses where course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));

		if ($a = $stmt->fetch()) {
			$model = Course::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * publishes course
	 * @param int $courseId
	 * @param bool $isPublished
	 */
	public function publishCourse($courseId, $isPublished) {
		$query = "UPDATE courses SET is_published = ? WHERE course_id = ?";
		
		$stmt = $this->prepare($query);
		$parameters = array(
			$isPublished ? 1: 0, $courseId
			);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * deletes complete curriculum of a course
	 * sections, lessons, content!!
	 * @param int $courseId
	 */
	public function deleteCurriculum($courseId) {
		// content
		$query = "
		delete from content_objects where object_id in (
		SELECT l.content_object_id FROM lessons l, sections s WHERE 
		l.section_id = s.section_id and s.course_id = ?
		)";
		$stmt = $this->prepare($query);
		$parameters = array(
			$courseId
			);
		$stmt = $this->execute($stmt, $parameters);

		// lessons
		$query = "
delete from lessons where section_id in (
SELECT section_id FROM sections WHERE course_id = ?
		)";
		$stmt = $this->prepare($query);
		$parameters = array(
			$courseId
			);
		$stmt = $this->execute($stmt, $parameters);

		// sections
		$query = "
	delete from sections where course_id = ?
		";
		$stmt = $this->prepare($query);
		$parameters = array(
			$courseId
			);
		$stmt = $this->execute($stmt, $parameters);
		
	
	}
	
	/**
	 * increases/decreases numSections of course
	 * @param int $courseId
	 * @param int $increase (+1 or -1)
	 */
	public function increaseCourseNumSections($courseId, $increase) {
		$query = "UPDATE courses SET num_sections = num_sections + ? WHERE course_id = ?";
		
		$stmt = $this->prepare($query);
		$parameters = array(
			$increase,
			$courseId
			);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * updates the rank of a lesson inside a section,
	 * e.g. 3rd lesson in section 2
	 * @param int $lessonId
	 * @param int $rank
	 */
	public function updateLessonRanks($lessonId, $sectionRank, $lessonRank) {
		$query = "UPDATE lessons SET section_rank = ?, rank = ? WHERE lesson_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
				$sectionRank,
				$lessonRank,
				$lessonId
		);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * updates the rank of a section
	 * @param int $sectionId
	 * @param int $rank
	 */
	public function updateSectionRank($sectionId, $rank) {
		$query = "UPDATE sections SET rank = ? WHERE section_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
				$rank,
				$sectionId
		);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * updates numlessons a section
	 * @param int $sectionId
	 * @param int $numLessons
	 */
	public function updateSectionNumLessons($sectionId, $numLessons) {
		$query = "UPDATE sections SET num_lessons = ? WHERE section_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
				$numLessons,
				$sectionId
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * increases/decreases numLessons of course
	 * @param int $courseId
	 * @param int $increase (+1 or -1)
	 */
	public function increaseCourseNumLessons($courseId, $increase) {
		$query = "UPDATE courses SET num_lessons =  num_lessons + ? WHERE course_id = ?";
		
		$stmt = $this->prepare($query);
		$parameters = array(
			$increase,
			$courseId
			);
		$stmt = $this->execute($stmt, $parameters);
	}

	/**
	 * increases/decreases numLessons of section
	 * @param int $sectionId
	 * @param int $increase (+1 or -1)
	 */
	public function increaseSectionNumLessons($sectionId, $increase) {
		$query = "UPDATE sections SET num_lessons =  num_lessons + ? WHERE section_id = ?";
	
		$stmt = $this->prepare($query);
		$parameters = array(
				$increase,
				$sectionId
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * increases/decreases numEnrollments of course
	 * @param int $courseId
	 * @param int $increase (+1 or -1)
	 */
	public function increaseCourseNumEnrollments($courseId, $increase) {
		$query = "UPDATE courses SET num_enrollments = num_enrollments + ? WHERE course_id = ?";
		
		$stmt = $this->prepare($query);
		$parameters = array(
			$increase,
			$courseId
			);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * creates Section 
	 * @param Section $model
	 * @return Section 
	 */	
	public function createSection($model) {
		$query = "INSERT INTO sections (name, course_id, title, rank, description) SELECT  ?, ?, ?, max(rank)+1, ? from sections where course_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array($model->name
			, $model->courseId
			, $model->title
			, $model->description
			, $model->courseId
			);
		$stmt = $this->execute($stmt, $parameters);
		$model->sectionId = $this->pdo->lastInsertId();
		return $model;
	}

	/**
	 * get Category by id
	 * @param int $id
	 * @return Category 
	 */	
	public function getCategoryById($id) {
		$dummy = new Category();
		$dummy->categoryId = $id;
		$category = $this->getFromCacheById($dummy);
		if (is_null($category)) {
			$query = "SELECT category_id, name, title, parent_id, ranking FROM categories where category_id = ?";
			$stmt = $this->prepare($query);
			$stmt = $this->execute($stmt, array($id));
			if ($a = $stmt->fetch()) {
				$category = Category::CreateModelFromRepositoryArray($a);
				$this->cacheObject($category);
				return $category;
			} else {
				return null;
			}
		} else {
			return $category;
		}
	}
	
	/**
	 * get Section by id
	 * @param int $id
	 * @return Section 
	 */	
	public function getSectionById($id) {
		$query = "SELECT section_id, name, course_id, title, description, rank, num_lessons FROM sections where section_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($id));
		if ($a = $stmt->fetch()) {
			$model = Section::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	
	/**
	 * updates progress
	 * @param Progress $progress
	 */
	public function updateProgress($progress) {
		$query = "UPDATE progress SET value = ? WHERE reference_id = ? and user_id = ? and type = ?";
		
		$stmt = $this->prepare($query);
		$parameters = array(
			$progress->value,
			$progress->referenceId,
			$progress->userId,
			$progress->type
			);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	
	/**
	 * updates Lesson
	 * @param Lesson $lesson
	 */
	public function updateLesson($lesson) {
		$query = "UPDATE lessons SET section_id = ?, rank = ?, section_rank = ? WHERE lesson_id = ?";
	
		$stmt = $this->prepare($query);
		$parameters = array(
				$lesson->sectionId,
				$lesson->rank,
				$lesson->sectionRank,
				$lesson->lessonId
		);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * shifts all lessons down one rank starting from a specific rank
	 * @param int $courseId
	 * @param int $fromRank
	 * @param int $toRank
	 * @param int $direction 1 or -1
	 */
	public function shiftLessons($courseId, $fromRank, $toRank, $direction) {
			$query = "UPDATE lessons SET rank = rank + ? WHERE course_id = ? and rank >= ? and rank < ?";
	
		$stmt = $this->prepare($query);
		$parameters = array(
				$direction,
				$courseId,
				$fromRank,
				$toRank
		);

		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * gets Progress
	 * @param int $userId
	 * @param int $referenceId
	 * @param ProgressTypes $progressType
	 * @return Progress
	 */	
	public function getProgress($userId, $referenceId, $progressType) {
		$query = 'SELECT user_id, reference_id, course_id, timestamp, type, value FROM progress where user_id = ? and reference_id = ? and type = ?';
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($userId, $referenceId, $progressType));
		
		if ($a = $stmt->fetch()) {
			$model = Progress::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	
	/**
	 * gets the next lesson that a user should finish in a course
	 * @param unknown $userId
	 * @param unknown $courseId
	 */
public function getNextLesson($userId, $courseId) {
	$query = 'SELECT l.course_id as course_id, rank, l.lesson_id as lesson_id, name, title, section_id, content_object_id, description, section_rank, image_name
	FROM lessons as l
	left outer join progress as p on l.lesson_id = p.reference_id  and p.type = ? and p.user_id = ?
	where l.course_id = ? and reference_id is null
	order by rank';
	$stmt = $this->prepare($query);
	$stmt = $this->execute($stmt, array(ProgressTypes::FinishedLesson, $userId, $courseId));
	
	if ($a = $stmt->fetch()) {
		$model = Lesson::CreateModelFromRepositoryArray($a);
		return $model;
	} else {
		return null;
	}
	
}
	
	/**
	 * creates Progress
	 * @param Progress $model
	 */	
	public function createProgress($model) {
		$query = "INSERT INTO progress (user_id, reference_id, course_id, timestamp, type, value) VALUES (?, ?, ?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array(
			$model->userId
			, $model->referenceId
			, $model->courseId
			, $model->timestamp
			, $model->type
			, $model->value
			
			);
		$stmt = $this->execute($stmt, $parameters);
	}
	
	/**
	 * retrieves all Progress of a user in a course
	 * @param int $userId
	 * @param int $courseId
	 * @return Array
	 */
	public function getCourseProgresses($userId, $courseId) {
		$query = "SELECT user_id, reference_id, course_id, timestamp, type, value FROM progress WHERE course_id = ? and user_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId, $userId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Progress::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	
	
	
	/**
	 * gets Section by name
	 * @param int $courseId
	 * @param string $name
	 * @return Section 
	 */	
	public function getSectionByName($courseId, $name) {
		$query = "SELECT section_id, name, course_id, title, description, rank, num_lessons FROM sections where course_id = ? and name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId, $name));

		if ($a = $stmt->fetch()) {
			$model = Section::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	
	
	/**
	 * retrieves all Sections
	 * @param int $courseId
	 * @return Array
	 */
	public function getCourseSections($courseId) {
		$query = "SELECT section_id, name, course_id, title, rank, description, num_lessons FROM sections WHERE course_id = ? order by rank";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Section::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	
	
	/**
	 * creates Lesson 
	 * @param Lesson $model
	 * @return Lesson 
	 */	
	public function createLesson($model) {
		$query = "INSERT INTO lessons (name, title, section_id, content_object_id, course_id, rank, description, image_name) SELECT ?, ?, ?, ?, ?, max(rank)+1, ?, ? from lessons where course_id = ?";
		$stmt = $this->prepare($query);
		$parameters = array(
			$model->name
			, $model->title
			, $model->sectionId
			, $model->contentObjectId
			, $model->courseId
			, $model->description
			, $model->imageName
			, $model->courseId
		);
		$stmt = $this->execute($stmt, $parameters);
		$model->lessonId = $this->pdo->lastInsertId();
		return $model;
	}

	/**
	 * get Lesson by name
	 * @param int $courseId
	 * @param string $name
	 * @return Lesson 
	 */	
	public function getLessonByName($courseId, $name) {
		$query = "SELECT lesson_id, name, title, section_id, content_object_id, course_id, description, rank, section_rank, image_name FROM lessons where name = ? AND course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name, $courseId));
		if ($a = $stmt->fetch()) {
			$model = Lesson::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	/**
	 * get Lesson by rank
	 * @param int $courseId
	 * @param int $rank
	 * @return Lesson 
	 */	
	public function getLessonByRank($courseId, $rank) {
		$query = "SELECT lesson_id, name, title, section_id, content_object_id, course_id, description, rank, section_rank, image_name FROM lessons where course_id = ? and rank = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId, $rank));
		if ($a = $stmt->fetch()) {
			$model = Lesson::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	/**
	 * retrieves all Lessons
	 * @param int $sectionId
	 * @return Array
	 */
	public function getLessonsBySectionId($sectionId) {
		$query = "SELECT lesson_id, name, title, section_id, content_object_id, course_id, rank, description, section_rank, image_name FROM lessons WHERE section_id = ? ORDER BY rank";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($sectionId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Lesson::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	/**
	 * retrieves all Lessons
	 * @param int $courseId
	 * @return Array
	 */
	public function getLessonsByCourseId($courseId) {
		$query = "SELECT lesson_id, name, title, section_id, content_object_id, course_id, rank, description, section_rank, image_name FROM lessons WHERE course_id = ? ORDER BY rank";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Lesson::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}

	/**
	 * retrieves all Sections
	 * @param int $courseId
	 * @return Array
	 */
	public function getSectionsByCourseId($courseId) {
		$query = "SELECT section_id, name, course_id, title, rank, description, num_lessons FROM sections WHERE course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Section::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	/**
	 * creates Enrollment 
	 * @param Enrollment $model
	 */	
	public function createEnrollment($model) {
		$query = "INSERT INTO enrollments (user_id, course_id, timestamp) VALUES ( ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->userId
			, $model->courseId
			, $model->timestamp
			);
		$stmt = $this->execute($stmt, $parameters);
	}
	/**
	 * gets Enrollment
	 * @param int $userId
	 * @param int $courseId
	 * @return Enrollment 
	 */	
	public function getEnrollment($userId, $courseId) {
		$query = "SELECT user_id, course_id, timestamp FROM enrollments where user_id = ? and course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($userId, $courseId));

		if ($a = $stmt->fetch()) {
			$model = Enrollment::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	
	/**
	 * gets number of Progresses of one course
	 * @param int $courseId
	 * @return int
	 */	
	public function getNumProgresses($courseId) {
		$query = "SELECT COUNT(*) as c FROM progress where course_id = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($courseId));

		if ($a = $stmt->fetch()) {
			return $a['c'];
		} else {
			return 0;
		}
	}
	
	/**
	 * creates Category 
	 * @param Category $model
	 * @return Category 
	 */	
	public function createCategory($model) {
		$query = "INSERT INTO categories (name, title, parent_id, ranking) VALUES ( ?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->name
	, $model->title
	, $model->parentId
	, $model->ranking
	);
		$stmt = $this->execute($stmt, $parameters);
		$model->categoryId = $this->pdo->lastInsertId();
		return $model;
	}
	
	/**
	 * retrieves all Categorys
	 * @return Array
	 */
	public function getCategorys() {
		$query = "SELECT category_id, name, title, parent_id, ranking FROM categories where parent_id is null order by parent_id, ranking";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array());
		$models = array();
		while ($a = $stmt->fetch()) {
			$models[] = Category::CreateModelFromRepositoryArray($a);
		}
		return $models;
	}
	
	/**
	 * gets Category by name
	 * @param string $name
	 * @return Category 
	 */	
	public function getCategoryByName($name) {
		$query = "SELECT category_id, name, title, parent_id, ranking FROM categories where name = ?";
		$stmt = $this->prepare($query);
		$stmt = $this->execute($stmt, array($name));

		if ($a = $stmt->fetch()) {
			$model = Category::CreateModelFromRepositoryArray($a);
			return $model;
		} else {
			return null;
		}
	}
	/**
	 * creates an attachment (-> links content to lesson)
	 * @param int $lessonId
	 * @param int $contentId
	 */
	public function createAttachment($lessonId, $contentId) {
		$query = "INSERT INTO attachments (lesson_id, content_id) VALUES (?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array(
			$lessonId
			, $contentId
		);
		$stmt = $this->execute($stmt, $parameters);
	}
}

?>