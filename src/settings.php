<?php
return [
    'settings' => [
    		
   		// Application settings
		// these settings will automatically be available in template rendering
		// you have to modify them
   		'application' => [
			'name' => 'savvago',
   			'claim' => 'you savvy?',
			'base' => 'http://localhost:8080/savvago/public/',
			'api' => 'http://localhost:8080/savvago/public/api/'
   		],
    		
    		
        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../public/templates/'
        ],

		// import
		'import' => [
			'import_path' => __DIR__ .'/../temp/'
		],
		// upload
		'upload' => [
			'upload_path' => __DIR__ .'/../public/upload/'
		],

		// Course settings
		'course' => [
			'default_image_name' => 'draft_course.png',
			'image_formats' => [
				'tile' => [
					'width' => 216,
					'height' => 121.5],
				'featured' => [
					'width' => 480,
					'height' => 270],
				'list' => [
					'width' => 125,
					'height' => 70],
				'promo' => [
					'width' => 750,
					'height' => 422]
				]
		],
		
		// data base
		'db' => [
			'host' => 'localhost',
			'dbname' => 'savvago',
			'user' => 'root',
			'pass' => ''
		],
    		
  		// caching
   		'cache' => [
   				'service' => __DIR__ . '/../cache/service/'
   		],
		
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];
