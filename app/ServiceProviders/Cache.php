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
		$settings = app()->getConfig('settings');

		$this->container['cache'] = function ($c) use($settings) {
			return function($driver = 'default') use($settings) {
				return new Redis(new Client($settings['cache'][$driver]));
			};
		};

		return $next($request, $response);
	}

}