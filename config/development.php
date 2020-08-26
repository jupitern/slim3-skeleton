<?php

return [
    // debug options
    'debug'         => true,    // used for error display
    'consoleOutput' => true,    // display console output
    'baseUrl'       => 'http://localhost:8080/',   // url config. Url must end with a slash '/'
    'indexFile'     => false,
    'slim' => [
        'settings' => [
            'routerCacheFile' => false,
        ],
    ],
    'services' => [
		/*
		'db' => [
            'provider' => \Jupitern\Slim3\ServiceProviders\IlluminateDatabase::class,
            'settings' => [
                'driver'    => 'mysql',
                'host'      => '',
                'database'  => '',
                'username'  => '',
                'password'  => '',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'profiling' => true,
            ],
        ],
        'db' => [
            'provider' => \Jupitern\Slim3\ServiceProviders\MongoDb::class,
            'settings' => [
                'uri'  => '',
                'db'   => '',
                'options' => [
                    'username' => '',
                    'password' => '',
                ],
                'setGlobal' => true,
            ],
        ],
        'redis' => [
            'provider' => \Jupitern\Slim3\ServiceProviders\Redis::class,
            'settings' => [
                'scheme'   => 'tcp',
                'host'     => '',
                'port'     => 6379,
                'database' => 0,
                'password' => ''
            ],
        ],
		*/
    ],
];
