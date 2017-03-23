<?php

// create journey
$app->post('/api/journeys', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$input = getRequestObject();

	$journeyService = $this->serviceContainer['journeyService'];
	$apiResult = $journeyService->createJourney($input);

	return $apiResult->toJson();
});

// update journey
$app->post('/api/journeys/{id}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$input = getRequestObject();

	$journeyService = $this->serviceContainer['journeyService'];
	$apiResult = $journeyService->updateJourney($args["id"], $input);

	return $apiResult->toJson();
});

// get all journeys
$app->get('/api/journeys', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$journeyService = $this->serviceContainer['journeyService'];
	$journeys = $journeyService->getJourneys();

	return json_encode($journeys);
});

// get one journey
$app->get('/api/journeys/{id}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$journeyService = $this->serviceContainer['journeyService'];
	$journey = $journeyService->getJourney($args["id"]);

	return json_encode($journey);
});
	
// delete one journey
$app->delete('/api/journeys/{id}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$journeyService = $this->serviceContainer['journeyService'];
	$apiResult = $journeyService->deleteJourney($args["id"]);

	return $apiResult->toJson();
});

// get journeys that match certain tags
$app->get('/api/journeys/matching/{tags}', function ($request, $response, $args) {
	checkIsAuthenticated($this);

	$journeyService = $this->serviceContainer['journeyService'];
	$journeys = $journeyService->getMatchingJourneys($args['tags']);

	return json_encode($journeys);
});
