<?php

namespace App\ServiceProviders;
use Lib\Framework\App;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ChromePHPHandler;
use Psr\Log\LoggerInterface;

class Monolog
{

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
		app()->getContainer()[LoggerInterface::class] = function($c) {
			return function($name = null, $logFilePath = null) {

				$app = app();
				if ($name === null) {
					$name = $app->console ? 'console' : 'app';
				}
				if ($logFilePath === null) {
					$logFilePath = $app->getConfig("settings.appLogFilePath");
				}
				$logger = new Logger($name);
				$logger->pushHandler(new StreamHandler($logFilePath, Logger::INFO));
				if ((bool)$app->getConfig("settings.debug")) {
					$logger->pushHandler(new ChromePHPHandler(Logger::DEBUG));
				}
				return $logger;
			};
		};

		return $next($request, $response);
    }

}