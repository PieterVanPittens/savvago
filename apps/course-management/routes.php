<?php
$appName = "course-management";

// list of users
$this->app->get('/apps/'.$appName, function ($request, $response, $args) {
	
	$page = new Page();
	$page->title = 'Course Management';
	$page->mainView = 'index.phtml';
	$this->viewData->data["page"] = $page;
	
	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});


?>