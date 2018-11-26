<?php

namespace App\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class Flash
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
        /** @var Twig $view */
        $view = app()->getContainer()[Twig::class];
//        $plages = app()->getContainer()[Plates::class];
        $message = app()->getContainer()[Messages::class];
        
        $message = \App\Helpers\Flash::getMessage($message);
        $view->getEnvironment()->addGlobal('message', $message);
        
		return $next($request, $response);
	}
}
