<?php


// like lesson
$app->post('/api/lessons/{id}/like', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$markService = $this->serviceContainer['markService'];
	$apiResult = $markService->likeLesson($args['id']);

	return $apiResult->toJson();
});

// check lesson
$app->post('/api/lessons/{id}/check', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$markService = $this->serviceContainer['markService'];
	$apiResult = $markService->checkLesson($args['id']);

	return $apiResult->toJson();
});

// like journey
$app->post('/api/journeys/{id}/like', function ($request, $response, $args) {
	checkIsAuthenticated($this);
	
	$markService = $this->serviceContainer['markService'];
	$apiResult = $markService->likeJourney($args['id']);
	
	return $apiResult->toJson();
});
	
// check journey
$app->post('/api/journeys/{id}/check', function ($request, $response, $args) {
	checkIsAuthenticated($this);
	
	$markService = $this->serviceContainer['markService'];
	$apiResult = $markService->checkJourney($args['id']);
	
	return $apiResult->toJson();
});
		
		