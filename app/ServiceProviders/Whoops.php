<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\RunInterface;

class Whoops
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		if (class_exists(RunInterface::class)) {
			$whoops = new \Whoops\Run;
			$whoops->allowQuit(false);
			$handler = new \Whoops\Handler\PrettyPageHandler;
			$handler->setPageTitle("Whoops! There was a problem.");
			$whoops->pushHandler($handler);
			$whoops->register();

			app()->getContainer()[\Whoops\Run::class] = $whoops;
		}

		return $next($request, $response);
	}

}