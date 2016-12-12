<?php namespace App\ServiceProviders;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class Monolog
{
	private $container;

	public function __construct($container) {
		$this->container = $container;
    }

    public function __invoke($request, $response, $next)
    {
		$this->container['logger'] = function($c) {
			return function($name = null, $logFilePath = null) {

				$app = \App\App::instance();
				if ($name === null) {
					$name = $app->console ? 'console' : 'app';
				}
				if ($logFilePath === null) {
					$logFilePath = $app->getConfig("settings.appLogFilePath");
				}
				$logger = new \Monolog\Logger($name);
				$file_handler = new \Monolog\Handler\StreamHandler($logFilePath);
				$logger->pushHandler($file_handler);
				return $logger;
			};
		};

		return $next($request, $response);
    }

}