<?php
// get all comments of a lesson
$app->get('/api/lessons/{id}/comments', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$commentService = $this->serviceContainer['commentService'];
	$comments = $commentService->getLessonComments($args['id']);

	return json_encode($comments);
});

// adds comment to lesson
$app->post('/api/lessons/{id}/comments', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$input = getRequestObject();
	$commentText = '';
	if (isset($input->comment)) {
		$commentText = $input->comment;
	}
	$commentService = $this->serviceContainer['commentService'];
	$apiResult = $commentService->commentLesson($args['id'], $commentText);

	return $apiResult->toJson();
});
	
// get all comments of a journey
$app->get('/api/journeys/{id}/comments', function ($request, $response, $args) {
	checkIsAuthenticated($this);
	
	$commentService = $this->serviceContainer['commentService'];
	$comments = $commentService->getJourneyComments($args['id']);
	
	return json_encode($comments);
});

// adds comment to journey
$app->post('/api/journeys/{id}/comments', function ($request, $response, $args) {
	checkIsAuthenticated($this);
	
	$input = getRequestObject();
	$commentText = '';
	if (isset($input->comment)) {
		$commentText = $input->comment;
	}
	$commentService = $this->serviceContainer['commentService'];
	$apiResult = $commentService->commentJourney($args['id'], $commentText);
	
	return $apiResult->toJson();
});
			

