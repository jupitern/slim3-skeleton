<?php

namespace app\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Lib\Framework\App;
use Lib\Auth\Auth;

class CheckAccess
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		if (!Auth::loggedIn()) {
			return $response->withRedirect(App::instance()->url('auth/login'), 403);
		}
		if (!Auth::hasAccess($request->getUri()->getPath())) {
			return $response->write(
				App::instance()->resolve('view')->render('error::403', [
					"message" => "You don't have permissions to access this resource"
				])
			);
		}

		return $next($request, $response);
	}
}