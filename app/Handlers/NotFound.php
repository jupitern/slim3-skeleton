<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use League\Plates\Engine;

final class NotFound extends \Slim\Handlers\NotFound
{
	protected $logger;

	public function __construct(Logger $logger = null)
	{
		$this->logger = $logger;
	}

	public function __invoke(Request $request, Response $response)
	{
		$app = app();

		// Log the message
		if ($this->logger) {
			$this->logger->error("URI '".$request->getUri()->getPath()."' not found");
		}

		if ($app->console) {
			$response->write("Error: request does not match any command::method or mandatory params are not properly set\n");
		}

		$resp = $app->resolve(Engine::class)->render('error::404');
		$response->withStatus(404)->write($resp);

		return parent::__invoke($request, $response);
	}



}