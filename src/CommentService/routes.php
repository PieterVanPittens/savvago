<?php
// get all comments of a lesson
$app->get('/api/lessons/{id}/comments', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$commentService = $this->serviceContainer['commentService'];
	$comments = $commentService->getLessonComments($args['id']);

	return json_encode($comments);
});