<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

final class NotFound extends \Slim\Handlers\NotFound
{
	protected $logger;

	public function __construct(Logger $logger = null)
	{
		$this->logger = $logger;
	}

	public function __invoke(Request $request, Response $response)
	{
		$app = \Lib\Framework\App::instance();

		// Log the message
		if ($this->logger) {
			$this->logger->error("URI '".$request->getUri()->getPath()."' not found");
		}

		if ($app->console) {
			$response->write("Error: request does not match any command::method or mandatory params are not properly set\n");
		}

		$resp = $app->resolve('view')->render('error::404');
		$response->withStatus(404)->write($resp);

		return parent::__invoke($request, $response);
	}



}