<?php
/**
 * converts underscore string to Camelcase string
 * translates database field names to object propery names
 */
function underscore2Camelcase($str) {
	$words = explode('_', strtolower($str));

	$return = '';
	foreach ($words as $word) {
		$return .= ucfirst(trim($word));
	}
	$return = lcfirst($return);
	return $return;
}


class GrapesException extends Exception {
	/**
	 * Message
	 * @var Message
	 */
	public $apiMessage;
	
	
	function __construct($text) {
		$this->setError($text);
	}
	
	/**
	 * sets an error message to this exception
	 * @param string $text
	 */
	function setError($text) {
		$this->apiMessage = new Message();
		$this->apiMessage->type = MessageTypes::Error;
		$this->apiMessage->text = $text;
	}
	

}

class BusinessException extends GrapesException {
}

class ModelException extends GrapesException {

	public $modelErrors = array();

	public function addModelError($propertyName, $message) {
		$this->modelErrors[$propertyName] = $message;
	}

	public function hasModelErrors() {
		return count($this->modelErrors) > 0;
	}
}
class RepositoryException extends GrapesException {
}
class ManagerException extends GrapesException {
}
class PluginException extends GrapesException {
}
class WebApiException extends GrapesException {
}
class ValidationException extends GrapesException {
}
/**
 * Ressource was not found
 * this exception will result in 404 error code in API Response
 *
 */
class NotFoundException extends GrapesException {
}
class UnauthorizedException extends GrapesException {
	
}

/**
 * interface for all models
 *
 */
interface iModel {

	/**
	 * returns id of this model
	 */
	public function getId();
	
}


/**
 * abstract base Model
 *
 */
class BaseModel {

	/**
	 * getter
	 * @param unknown $property
	 */
	public function __get($property) {
            if (property_exists($this, $property)) {
                return $this->$property;
            }
    }

    /**
     * setter
     * @param unknown $property
     * @param unknown $value
     */
    public function __set($property, $value) {
        if (property_exists($this, $property)) {
			$this->$property = $value;
        }
    }

    /**
     * converts object to json
     * @return string
     */
	public function toJson() {
		$array = (array) $this;
		return json_encode($array);
	
	}
	
	/**
	 * Creates a model and populates it with data from a repository row
	 */
	public static function createModelFromRepositoryArray($array) {	
		$rc = new ReflectionClass(get_called_class());
		$model = $rc->newInstance();
		
		// convert database field names to property names
		$properties = array();
		foreach($array as $key => $value) {
			$properties[underscore2Camelcase($key)] = $value;
		}
		
		foreach($model as $key => $value) {
			if (array_key_exists($key, $properties)) {
				$model->$key = $properties[$key];
			}
		}
		return $model;
	}
	
	/**
	 * Creates a model and populates it with data from json
	 */
	public static function createModelFromJson($json) {	
		$rc = new ReflectionClass(get_called_class());
		$model = $rc->newInstance();
		
		if ($json == "") {
			return null;
		}
				
		$jsonObject = json_decode($json);
		if ($jsonObject == null) { // json cannot be parsed
			throw new WebApiException("Request does not contain a valid JSON String");
		}
		foreach ($jsonObject AS $key => $value) {
			$model->{$key} = $value;
		}
		return $model;
	}
	
	/**
	 * retrieves objectname including name of class
	 */
	public function getObjectName() {
		return get_called_class().$this->name;		
	}
	
	public function getClassName() {
		return get_called_class();
	}
}

class ModelHelper {

	/**
	 * converts title to name
	 * @param string $title
	 * @return string
	 */
	static function convertTitleToName($title) {
		$name = preg_replace('/^-+|-+$/', '', strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title)));
		return $name;
	}
}


/**
 * Models that are supposed to be cached need to implement iCachable
 */
interface iCachable {
	public function getModelType();
	public function getId();
}



class Course extends BaseModel implements iCachable {
	public $courseId;
	public $userId;
	public $universityId;
	public $name;
	public $urls = array();
	protected $title;
	public $subtitle;
	public $description;
	public $imageName;
	public $categoryId;
	public $numSections;
	public $numLessons;
	public $numEnrollments;
	public $isPublished;

	public function setTitle($title) {
		$this->title = $title;
		$this->name = ModelHelper::convertTitleToName($title);
	}
	
	/**
	 * list of all course progresses of one user
	 */
	public $progresses = array();


	/**
	 * promo video id
	 */
	public $videoId;
	/**
	 * @var University;
	 */
	public $university;
	
	public $sections;
	
	/**
	 * @var Category
	 */
	public $category;

	/**
	 * parent category
	 * @var Category
	 */
	public $parentCategory;
	
	/**
	 * @var ContentObject
	 */
	public $video;
	
	/**
	 * @var User
	 */
	public $user;
	
	
	public function getModelType() {
		return ModelTypes::Course;
	}
	public function getId() {
		return $this->courseId;
	}
	
}

class Section extends BaseModel {
	public $sectionId;
	public $name;
	public $courseId;
	public $rank; 
	public $description;
	public $numLessons;
	
	protected $title;
	public function setTitle($title) {
		$this->title = $title;
		$this->name = ModelHelper::convertTitleToName($title);
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @var Course
	 */
	public $course;
	
	public $urls = array();
	public $lessons;
	
	public $progresses = array();

}

class Lesson extends BaseModel implements JsonSerializable {
	public $lessonId;
	public $sectionId;
	public $courseId;
	public $name;
	public $contentObjectId;
	public $rank; 
	public $description; 
	public $sectionRank;
	public $imageName;

	protected $title;
	public function setTitle($title) {
		$this->title = $title;
		$this->name = ModelHelper::convertTitleToName($title);
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public $progresses = array();
	public $urls = array();

	
	/**
	 * @var Course
	 */
	public $course;
	
	
	/**
	 * @var ContentObject
	 */
	public $content;
	
	/**
	 * @var Section
	 */
	public $section;
	
	
	public function jsonSerialize() {
		return [
			'lessonId' => $this->lessonId,
			'sectionId' => $this->sectionId,
			'courseId' => $this->courseId,
			'name' => $this->name,
			'contentObjectId' => $this->contentObjectId,
			'rank' => $this->rank,
			'description' => $this->description,
			'title' => $this->title
			];
	}
	
}


/**
 * Page
 */
class Page {
	public $title;
	public $mainView;
}

class Enrollment extends BaseModel {
	public $courseId;
	public $userId;
	public $timestamp;
}

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

/**
 * progress a user made in a course, topic, learning objects
 */
class Progress extends BaseModel {
	public $userId;
	public $referenceId;
	public $courseId;
	public $timestamp;
	/**
	 * @var ProgressTypes
	 */
	public $type;
	public $value;

}

/**
 * Progress Types
 * enum
 */
abstract class ProgressTypes {
	const FinishedSectionsTotal = 1;
	const FinishedLesson = 2;
	/**
	 * number of lessons finished in course
	 */	 
	const CourseFinishedLessonsTotal = 3;
	/**
	 * number of lessons finished in section
	 */	 
	const SectionFinishedLessonsTotal = 4;
	/**
	 * enrolled to course
	 */	 
	const Enrolled = 5;
}


/**
 * ModelTypes
 * enum
 */
abstract class ModelTypes {
	const Course = 1;
}


?>