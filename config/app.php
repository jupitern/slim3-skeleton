<?php

use Jupitern\Slim3\App;
use Psr\Log\LogLevel;

return [
    'env' => App::DEVELOPMENT,
    // default timezone & locale
    'locale' => 'pt_PT',
    'timezone' => 'UTC',
    'slim' => [
        'settings' => [
            'addContentLengthHeader' => false,
            'determineRouteBeforeAppMiddleware' => true,
        ]
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
    'services' => [
        'slashtrace' => [
            'provider' => \Jupitern\Slim3\ServiceProviders\SlashTrace::class,
        ],
        'logger' => [
            'provider' => \Jupitern\Slim3\ServiceProviders\Monolog::class,
            'settings' => [
                [
                    'type'      => 'file',
                    'enabled'   => true,
                    'level'     => LogLevel::DEBUG,
                    'path'      => STORAGE_PATH . 'logs' . DS . date('Ymd') . ".log",
                ],
            ]
        ],
        'view' => [
            'provider' => \Jupitern\Slim3\ServiceProviders\Plates::class,
            'settings' => [
                'templates' => [
                    'http'    => VIEWS_PATH . DS . "http",
                    'console' => VIEWS_PATH . DS . "console",
                    'mail'    => VIEWS_PATH . DS . "mail",
                ],
            ],
        ],
        'fs_local' => [
            'provider' => \Jupitern\Slim3\ServiceProviders\FileSystem::class,
            'settings' => [
                'driver' => 'local',
                'root'   => ROOT_PATH,
            ]
        ],
        /*
        'mail' => [
            'provider' => \Jupitern\Slim3\ServiceProviders\Mailer::class,
            'on' => 'console',
            'settings' => [
                'host'     => '',
                'port'     => 25,
                'secure'   => '',
                'username' => '',
                'password' => '',
                'from'     => '',
                'fromName' => '',
                'replyTo'  => '',
            ]
        ],
        */
    ],
    'middleware' => [
        // middlewareClass => scenario
        \Jupitern\Slim3\Middleware\Session::class => "http", // "http,console" to run on both scenarios
//        \Jupitern\Slim3\Middleware\ValidateJson::class => "http",
    ],
    // app specific settings
    'app' => [

    ],
];

