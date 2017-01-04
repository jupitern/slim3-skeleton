<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Monolog
{
	private $container;

	public function __construct($container) {
		$this->container = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
		$this->container['logger'] = function($c) {
			return function($name = null, $logFilePath = null) {

				$app = \Lib\App::instance();
				if ($name === null) {
					$name = $app->console ? 'console' : 'app';
				}
				if ($logFilePath === null) {
					$logFilePath = $app->getConfig("settings.appLogFilePath");
				}
				$logger = new \Monolog\Logger($name);
				$logger->pushHandler(new \Monolog\Handler\StreamHandler($logFilePath, Logger::INFO));
				if ((bool)$app->getConfig("settings.debug")) {
					$logger->pushHandler(new \Monolog\Handler\ChromePHPHandler(Logger::DEBUG));
				}
				return $logger;
			};
		};

		return $next($request, $response);
    }

}