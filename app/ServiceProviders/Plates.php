<?php namespace App\ServiceProviders;

use \League\Plates\Engine;
use \League\Plates\Extension\URI;

class Plates
{
	private $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function __invoke($request, $response, $next)
	{
		$basePath = $request->getUri()->getBasePath();

		$this->container['view'] = function ($c) use($basePath) {
			return function($directory = null, $fileExtension = 'php') use($basePath) {
				$plates = new Engine($directory, $fileExtension);
				$plates->loadExtension(new URI($basePath));

				$templatesPath = \App\App::instance()->getConfig('settings.templates');
				foreach ($templatesPath as $name => $path) {
					$plates->addFolder($name, $path, true);
				}
				return $plates;
			};
		};

		return $next($request, $response);
	}

}