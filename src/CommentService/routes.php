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
	


