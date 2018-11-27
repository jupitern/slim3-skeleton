<?php
return [
	'settings' => [
		'env' => \Lib\Framework\App::DEVELOPMENT,
		'addContentLengthHeader' => false,
		// default timezone & locale
		'timezone' => 'Europe/Lisbon',
        'locale' => 'pt_PT',
		// Only set this if you need access to route within middleware
		'determineRouteBeforeAppMiddleware' => false,
		// log file path
        'log' => [
            // log file path
            'file' => STORAGE_PATH."logs".DS."app_".date('Ymd').".log",
        ],
		// template folders
		'templates' => [
			'error' => RESOURCES_PATH."views".DS."http".DS."error",
			'console' => RESOURCES_PATH."views".DS."console",
			'site' 	=> RESOURCES_PATH."views".DS."http".DS."site",
			'mail' 	=> RESOURCES_PATH."views".DS."mail",
		],
        // setting for twig extension
        'twig' => [
            'cache' => STORAGE_PATH . 'twig',
            'debug' => true,
            'auto_reload' => true,
        ],
		'session' => [
			'name' => 'app',
			'lifetime' => 7200,
			'path' => '/',
			'domain' => null,
			'secure' => false,
			'httponly' => true,
			'cache_limiter' => 'nocache',
			'filesPath' => STORAGE_PATH.'sessions',
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
    App\ServiceProviders\Monolog::class => 'http,console',
    App\ServiceProviders\Whoops::class => 'http',
    App\ServiceProviders\Collision::class => 'console',
		App\ServiceProviders\Plates::class => 'http',
		App\ServiceProviders\Twig::class => 'http',
		App\ServiceProviders\Eloquent::class => 'http,console',
		App\ServiceProviders\FileSystem::class => 'http,console',
		App\ServiceProviders\Mailer::class => 'http,console',
		App\ServiceProviders\Cache::class => 'http,console',
    App\ServiceProviders\Flash::class,
		App\ServiceProviders\Jobby::class,
	],
	// add your middleware here
	'middleware' => [
		App\Middleware\Session::class => 'http',
    App\Middleware\Flash::class,
	],

];
