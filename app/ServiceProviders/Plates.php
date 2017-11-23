<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\Plates\Engine;

class Plates
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		app()->getContainer()[Engine::class] = function ($c) {
			return function($directory = null, $fileExtension = 'php') {

				$plates = new Engine($directory, $fileExtension);
				$templatesPath = app()->getConfig('settings.templates');
				foreach ($templatesPath as $name => $path) {
					$plates->addFolder($name, $path, true);
				}
				return $plates;
			};
		};

		return $next($request, $response);
	}

}