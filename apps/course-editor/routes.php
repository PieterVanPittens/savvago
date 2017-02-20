<?php
$appName = "course-editor";


// course editor
$this->app->get('/apps/'.$appName.'/{courseId}', function($request, $response, $args) {

	$courseService = $this->serviceContainer['courseService'];
	$course = $courseService->getCourseById($args['courseId'], true);

	$this->viewData->data['course'] = $course;

	$page = new Page();
	$page->title = "Course Editor - " . htmlspecialchars($course->title);
	$page->mainView = 'index.phtml';
	$this->viewData->data["page"] = $page;
	
	
	// Render index view
	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});

// course editor
$this->app->get('/apps/'.$appName, function ($request, $response, $args) {
	
	$page = new Page();
	$page->title = 'Course Editor';
	$page->mainView = 'index.phtml';
	$this->viewData->data["page"] = $page;
	
	$course = new Course();
	$course->courseId = 41;
	$this->viewData->data["course"] = $course;
	
	return $this->renderer->render($response, 'master.phtml', $this->viewData->data);
});






?>