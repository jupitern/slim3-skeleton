<?php
return [
	'settings' => [
		// url config. Url must end with a slash '/'
		'baseUrl' => 'http://localhost:8888/',
		'indexFile' => true,
		// display errors details
		'displayErrorDetails' => true,
		// debug mode
		'debug' => true,
		// default timezone
		'timezone' => 'Europe/Lisbon',
		// Only set this if you need access to route within middleware
        'determineRouteBeforeAppMiddleware' => false,
		// log file path
		'appLogFilePath' => STORAGE_PATH."logs\\app_".date('Ymd').".log",
		// template folders
		'templates' => [
			'error' => RESOURCES_PATH."views\\error",
			'mail' 	=> RESOURCES_PATH."views\\mail",
			'site' 	=> RESOURCES_PATH."views\\site",
		],
		'session' => [
			'name' => 'app',
			'lifetime' => 7200,
			'path' => null,
			'domain' => null,
			'secure' => false,
			'httponly' => true,
			'cache_limiter' => 'nocache',
		],
		// database configs
		'database' => [
			// default db connection settings
			'default' => [
				'driver'    => 'mysql',
				'host'      => '',
				'database'  => '',
				'username'  => '',
				'password'  => '',
				'charset'   => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'prefix'    => ''
			],
			// sqlsrv config
//			'default' => array(
//				'driver' 	=> 'sqlsrv',
//				'host' 		=> '',
//				'database' 	=> '',
//				'username' 	=> '',
//				'password' 	=> '',
//				'prefix' 	=> '',
//			),
			// add another db connection here
		],
		// storage settings
		'filesystem' => [
			'local' => [
				'driver' 	=> 'local',
				'root'   	=> STORAGE_PATH,
			],
			'ftp' => [
				'driver'	=> 'ftp',
				'host' 		=> '',
				'username' 	=> '',
				'password' 	=> '',
				'port' 		=> 21,
				'root' 		=> '/',
				'passive' 	=> true,
				'ssl' 		=> false,
				'timeout' 	=> 30,
			],
		],
		'mail' => [
			'default' => [
				'host'    	=> '',
				'port'      => 25,
				'secure'	=> '',
				'username'  => '',
				'password'  => '',
				'from'		=> '',
				'fromName'	=> '',
				'replyTo'	=> '',
			]
		],
	],
	// add your service providers here
	'providers' => [
		'\\App\\ServiceProviders\\Whoops',
		'\\App\\ServiceProviders\\Monolog',
		'\\App\\ServiceProviders\\Plates',
		'\\App\\ServiceProviders\\Eloquent',
		'\\App\\ServiceProviders\\FileSystem',
		'\\App\\ServiceProviders\\Mailer',
	],
	// add your middleware here
	// middleware bellow are called for every route
	'middleware' => [
		'\\App\\Middleware\\Session',
	],

];