<?php

// DIC configuration


$container = $app->getContainer();


// view Data
$container['viewData'] = function ($c) {
    return new ViewData();
};


// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// user
$container['userContainer'] = function ($c) {
	$user = new User();
	$user->userId = 0;
	$user->name = "Guest";
	$service = new UserContainer();
	$service->setUser($service->getGuest());
	return $service;
};

$container['serviceContainer'] = function ($c) {
	require __DIR__ . '/serviceContainer.php';
	$serviceContainer['settings'] = $c['settings'];
	$serviceContainer['contextUser'] = $c['userContainer']->getUser();
	return $serviceContainer;
};

?>