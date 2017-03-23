<?php

// get lessons
$app->get('/api/lessons', function ($request, $response, $args) {
	//checkIsAuthenticated($this);

	$lessonService = $this->serviceContainer['lessonService'];

	$lessons = $lessonService->getLessons();

	return json_encode($lessons);

});
// get one lesson
$app->get('/api/lessons/{id}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$lessonService = $this->serviceContainer['lessonService'];
	$lesson = $lessonService->getLesson($args["id"]);

	return json_encode($lesson);
});

// finish lesson
$app->post('/api/lessons/{lessonId}/finish', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$courseManager = $this->serviceContainer['courseManager'];
	$courseService = $this->serviceContainer['courseService'];
	$lesson = $courseManager->getLessonById($args['lessonId'], false);
	$result = $courseService->finishLesson($lesson);
	return $result->toJson();
});

// create lesson
$app->post('/api/lessons', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$input = getRequestObject();
	
	$lessonService = $this->serviceContainer['lessonService'];
	$apiResult = $lessonService->createLesson($input);

	return $apiResult->toJson();
});

// update lesson
$app->post('/api/lessons/{id}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$input = getRequestObject();

	$lessonService = $this->serviceContainer['lessonService'];
	$apiResult = $lessonService->updateLesson($args["id"], $input);

	return $apiResult->toJson();
});

// get lessons that match certain tags
$app->get('/api/lessons/matching/{tags}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$lessonService = $this->serviceContainer['lessonService'];
	$lessons = $lessonService->getMatchingLessons($args['tags']);

	return json_encode($lessons);
});

// delete one lesson
$app->delete('/api/lessons/{id}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$lessonService = $this->serviceContainer['lessonService'];
	$apiResult = $lessonService->deleteLesson($args["id"]);

	return $apiResult->toJson();
});
