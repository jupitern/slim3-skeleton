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
		$app = app();
		$appLogger = null;
		$appLoggerName = $app->console ? 'console' : 'app';
		$logFilePath = $app->getConfig("settings.appLogFilePath");

		if (!empty($logFilePath)) {
			$appLogger = $this->newLogger($logFilePath, $appLoggerName);
		}

		$app->getContainer()[LoggerInterface::class] = function($c) use($appLogger, $appLoggerName) {
			return function($logFilePath = null, $name = null, $level = LogLevel::DEBUG) use($appLogger, $appLoggerName) {

				if ($name == null) {
					$name = $appLoggerName;
				}

				return $logFilePath != null ? $this->newLogger($logFilePath, $name, $level) : $appLogger;
			};
		};

		return $next($request, $response);
    }


	private function newLogger($logFilePath = null, $name = null, $level = LogLevel::DEBUG)
	{
		$logger = new Logger($name);
		if (!empty($logFilePath)) {
			$handler = new StreamHandler($logFilePath, $level);
			$formatter = new \Monolog\Formatter\LineFormatter();
			$formatter->includeStacktraces(true);
			$handler->setFormatter($formatter);
			$logger->pushHandler($handler);
			if ((bool)app()->getConfig("settings.debug")) {
				$logger->pushHandler(new ChromePHPHandler($level));
			}
		}

		return $logger;
	}

}