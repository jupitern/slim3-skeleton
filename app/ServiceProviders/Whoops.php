<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\RunInterface;
use Whoops\Run;

class Whoops
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		if (class_exists(Run::class)) {
			$whoops = new Run;
			$whoops->allowQuit(false);
			$handler = new PrettyPageHandler;
			$handler->setPageTitle("Whoops! There was a problem.");
			$whoops->pushHandler($handler);
			$whoops->register();

			app()->getContainer()[RunInterface::class] = $whoops;
		}

		return $next($request, $response);
	}

}