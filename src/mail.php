<?php

class MailQueueItem {
	public $id;
	public $fromName;
	public $fromEmail;
	public $toName;
	public $toEmail;
	public $subject;
	public $message;
	public $maxAttempts;
	public $numAttempts;
	public $isSent;
	public $dateCreated;
	public $dateSent;
	public $dateLastAttempt;
}

/**
 * MailQueueRepository
 */
class MailQueueRepository extends BasePdoRepository {

	/**
	 * creates id 
	 * @param id $model
	 * @return id 
	 */	
	public function createMailQueueItem($model) {
		$query = "INSERT INTO email_queue (from_name, from_email, to_name, to_email, subject, message, max_attempts, num_attempts, is_sent, date_created, date_last_attempt, date_sent) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->prepare($query);
		$parameters = array($model->fromName
	, $model->fromEmail
	, $model->toName
	, $model->toEmail
	, $model->subject
	, $model->message
	, $model->maxAttempts
	, $model->numAttempts
	, $model->isSent
	, $model->dateCreated
	, $model->dateLastAttempt
	, $model->dateSent
	);
		$stmt = $this->execute($stmt, $parameters);
		$model->id = $this->pdo->lastInsertId();
		return $model;
	}
}

/**
 * EmailQueueManager
 */
class MailQueueManager extends BaseManager {

	/**
	 * creates a MailQueueItem
	 * @param MailQueueItem $mailQueueItem
	 */
	public function createMailQueueItem($mailQueueItem) {
		$mailQueueItem->maxAttempts = 3; // todo: config
		$mailQueueItem->numAttempts = 0;
		$mailQueueItem->isSent = false;
		$mailQueueItem->dateCreated = time();
		
		$this->repository->createMailQueueItem($mailQueueItem);
	}
}


?>