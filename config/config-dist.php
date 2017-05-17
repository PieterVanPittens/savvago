<?php
return [
    'settings' => [
    		
   		// Application settings
		// these settings will automatically be available in template rendering
		// you have to modify them
   		'application' => [
			'name' => '{{applicationName}}',
   			'claim' => '{{applicationClaim}}',
			'base' => '{{applicationBaseUri}}',
			'template' => 'templates/default/',
   			'api' => '{{applicationApiUri}}',
			// sender email
			// sender of automated emails sent to users
   			'senderEmail' => '{{applicationSenderEmail}}'
   		],
    		
    		
        'displayErrorDetails' => true, // set to false in production

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/default/'
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
			'host' => '{{dbHost}}',
			'dbname' => '{{dbName}}',
			'user' => '{{dbUser}}',
			'pass' => '{{dbPass}}'
		],
		
   		// security
   		'security' => [
   			// this is the secret key for hashing the passwords
   			// it will be randomly defined at installation time
   			// when you change the salt in a running system then all passwords become invalid,
   			// they cannot be matched anymore -> noone is able to login anymore
			'salt' => '{{securityPasswordSalt}}',
			// this is the key for creating login tokens
			// when you change this key in a running system then all active tokens will become invalid
			// that means all active users will be logged out
			'tokenKey' => '{{securityTokenKey}}'
   		],
		
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],
    ],
];
