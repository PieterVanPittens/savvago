<?php
// load an app
$app->get('/apps/{name}', function($request, $response, $args) {

	$appService = $this->serviceContainer['appService'];
	$app = $appService->getAppByName($args['name']);

	$this->viewData->data['app'] = $app;

	$page = new Page();
	$page->title = htmlspecialchars($app->title);
	$page->mainView = 'app.phtml';
	$this->viewData->data["page"] = $page;

	// Render index view
	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// homescreen of a user, showing all assigned apps
$app->get('/home', function ($request, $response, $args) {

	$page = new Page();
	$page->title = 'Home';
	$page->mainView = 'home.phtml';


	$appService = $this->serviceContainer['appService'];
	$apps = $appService->getHomeApps();


	$this->viewData->data['apps'] = $apps;
	$this->viewData->data['page'] = $page;

	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});