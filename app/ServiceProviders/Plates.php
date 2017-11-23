<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\Plates\Engine;
use League\Plates\Extension\URI;

class Plates
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		$basePath = $request->getUri()->getBasePath();

		app()->getContainer()[Engine::class] = function ($c) use($basePath) {
			return function($directory = null, $fileExtension = 'php') use($basePath) {

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