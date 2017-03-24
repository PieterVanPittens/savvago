<?php


/**
 * Content Manager
 */
class ContentManager extends BaseManager {

	/**
	 * ContentRepository
	 * @var ContentRepository
	 */
	protected $repository;

	/**
	 * @var iProviderPlugin
	 */
	private $storageProvider;
	
	
	/**
	 * constructor
	 * @param ContentRepository $repository
	 * @param iProviderPlugin $storageProvider
	 */
	function __construct(
			$repository
			, $storageProvider
			) {
				$this->repository = $repository;
				$this->storageProvider = $storageProvider;
	}
	
	/**
	 * creates a ContentObject
	 * @param $ContentObject $contentObject
	 */
	public function createContentObject($contentObject) {
		$this->repository->createContentObject($contentObject);
	}
	/**
	 * creates a ContentType
	 * @param $ContentType $contentType
	 */
	public function createContentType($contentType) {
		$this->repository->createContentType($contentType);
	}

	/**
	 * gets ContentType by name
	 * @param string $name
	 * @return ContentType
	 */
	public function getContentTypeByName($name) {
		if ($name == "") {
			throw new ParameterException("name is empty");
		}
		$model = $this->repository->getContentTypeByName($name);
		return $model;
	}

	/**
	 * gets ContentType by id
	 * @param int $id
	 * @return ContentType
	 */
	public function getContentType($id) {
		$model = $this->repository->getContentType($id);
		return $model;
	}
	
	
	/**
	 * gets ContentObjects of a lesson (attachments)
	 * @param int $lessonId
	 * @return array
	 */
	function getLessonAttachments($lessonId) {
		$attachments = $this->repository->getLessonAttachments($lessonId);
		foreach($attachments as $attachment) {
			$this->decodeContent($attachment);
		}
		return $attachments;
	}

	/**
	 * gets ContentObjects of a course
	 * @param int $courseId
	 * @return array
	 */
	function getCourseContents($courseId) {
		$contents = $this->repository->getCourseContents($courseId);
		return $contents;
	}

	/**
	 * decodes content field according to content type
	 * @param ContentObject $attachment
	 */
	private function decodeContent(ContentObject $attachment) {
		$attachment->content = json_decode($attachment->content);
	}

	/**
	 * gets contentobject by id
	 * @param int $objectId
	 * @return ContentObject
	 */
	function getContentObject($objectId) {
		$contentObject = $this->repository->getContentObjectById($objectId);
		$contentObject->url = $this->storageProvider->getAssetUrl($contentObject->name);
		return $contentObject;
	}
}

?>