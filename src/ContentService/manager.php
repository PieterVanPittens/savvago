<?php


/**
 * Content Manager
 */
class ContentManager extends BaseManager {

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

}

?>