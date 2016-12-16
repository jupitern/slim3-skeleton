<?php namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Session
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next, $next)
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {

			$settings = \App\App::instance()->getConfig('settings.session');

			$current = session_get_cookie_params();
			$lifetime = (int)($settings['lifetime'] ?: $current['lifetime']);
			$path     = $settings['path'] ?: $current['path'];
			$domain   = $settings['domain'] ?: $current['domain'];
			$secure   = (bool)$settings['secure'];
			$httponly = (bool)$settings['httponly'];
			session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
			session_name($settings['name']);
			session_cache_limiter($settings['cache_limiter']);
			session_start();
		}

		return $next($request, $response);
	}

}