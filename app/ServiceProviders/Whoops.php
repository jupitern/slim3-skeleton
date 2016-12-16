<?php namespace App\ServiceProviders;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class Whoops
{
	private $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		$whoops = new \Whoops\Run;
		$whoops->allowQuit(false);
		$handler = new \Whoops\Handler\PrettyPageHandler;
		$handler->setPageTitle("Whoops! There was a problem.");
		$whoops->pushHandler($handler);
		$whoops->register();

		$this->container['whoops'] = $whoops;

		return $next($request, $response);
	}

}