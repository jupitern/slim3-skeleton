<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\Plates\Engine;
use League\Plates\Extension\URI;

class Plates
{
	private $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		$basePath = $request->getUri()->getBasePath();

		$this->container['view'] = function ($c) use($basePath) {
			return function($directory = null, $fileExtension = 'php') use($basePath) {
				$plates = new Engine($directory, $fileExtension);
				$plates->registerFunction('url', function ($url, $showIndex = null, $includeBaseUrl = true) {
					return \Lib\Framework\App::instance()->url($url, $showIndex, $includeBaseUrl);
				});

				$templatesPath = \Lib\Framework\App::instance()->getConfig('settings.templates');
				foreach ($templatesPath as $name => $path) {
					$plates->addFolder($name, $path, true);
				}
				return $plates;
			};
		};

		return $next($request, $response);
	}

}