<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class NotFound extends \Slim\Handlers\NotFound
{

	public function __invoke(Request $request, Response $response)
	{
		$app = app();
		$container = $app->getContainer();

		// Log the message
		if ($container->has(LoggerInterface::class)) {
			$app->resolve(LoggerInterface::class)->error("URI '".$request->getUri()->getPath()."' not found");
		}

		if ($app->isConsole()) {
			return $response->write("Error: request does not match any command::method or mandatory params are not properly set\n");
		}

        if ($this->determineContentType($request) == 'application/json') {
		    return app()->error("URI '".$request->getUri()->getPath()."' not found", 404);
        }

		$resp = $app->resolve('view')->render('http::error', [
		    'code' => 404,
            'message' => "uri {$request->getUri()->getPath()} not found",
        ]);
        $response = $response->withStatus(404)->write($resp);

		return $response;
	}



}