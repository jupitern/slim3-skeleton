<?php

namespace App\ServiceProviders;
use Lib\Framework\App;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ChromePHPHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Monolog
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		app()->getContainer()[LoggerInterface::class] = function($c)  {
			return function($logFilePath = null, $name = null, $level = LogLevel::DEBUG) {

				$app = app();
				$name = $name ?? $app->console;
				$logFilePath = $logFilePath ?? $app->getConfig("settings.appLogFilePath");

				$logger = new Logger($name);

				if (!empty($logFilePath)) {
					$formatter = new \Monolog\Formatter\LineFormatter(null, null, true);
					$formatter->includeStacktraces(false);

					$handler = new StreamHandler($logFilePath, $level);
					$handler->setFormatter($formatter);

					$logger->pushHandler($handler);

					if ((bool)app()->getConfig("settings.debug")) {
						$handler2 = new ChromePHPHandler($level);

						$logger->pushHandler($handler2);
					}
				}

				return $logger;
			};
		};

		return $next($request, $response);
	}

}