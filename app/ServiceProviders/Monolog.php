<?php

namespace App\ServiceProviders;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Monolog implements ProviderInterface
{

	public static function register()
    {
        $app = app();
        $appName = $app->isConsole() ? 'console' : 'http';
        $logFilePath = $logFilePath ?? $app->getConfig("settings.log.file");

        $logger = new Logger($appName);

        if (!empty($logFilePath)) {
            $formatter = new LineFormatter(null, null, true);
            $formatter->includeStacktraces(false);

            $handler = new StreamHandler($logFilePath, LogLevel::DEBUG);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
        }

		app()->getContainer()[LoggerInterface::class] = function($c) use($appName) {
			return function($logFilePath = null, $name = null, $level = LogLevel::DEBUG) use($appName) {

				$app = app();
				$name = $name ?? $appName;
				$logFilePath = $logFilePath ?? $app->getConfig("settings.log.file");

				$logger = new Logger($name);
                $formatter = new LineFormatter(null, null, true);
                $formatter->includeStacktraces(false);
                $handler = new StreamHandler($logFilePath, $level);
                $handler->setFormatter($formatter);
                $logger->pushHandler($handler);

				return $logger;
			};
		};
    }

}