<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Naroga\RedisCache\Redis;
use Predis\Client;

class Cache
{
	private $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		$this->container['cache'] = function ($c) {
			return function($driver = 'default') {
				$settings = app()->getConfig('settings.cache');

				return new Redis(new Client($settings[$driver]));
			};
		};

		return $next($request, $response);
	}

}