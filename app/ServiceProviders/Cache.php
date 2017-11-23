<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Naroga\RedisCache\Redis;
use Predis\Client;
use Psr\SimpleCache\CacheInterface;

class Cache
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		app()->getContainer()[CacheInterface::class] = function ($c) {
			return function($driver = 'default') {
				$settings = app()->getConfig('settings.cache');

				return new Redis(new Client($settings[$driver]));
			};
		};

		return $next($request, $response);
	}

}