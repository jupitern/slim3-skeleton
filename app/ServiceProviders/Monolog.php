<?php namespace App\ServiceProviders;

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
				$file_handler = new \Monolog\Handler\StreamHandler($logFilePath);
				$logger->pushHandler($file_handler);
				return $logger;
			};
		};

		return $next($request, $response);
    }

}