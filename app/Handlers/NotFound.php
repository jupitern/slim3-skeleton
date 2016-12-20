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
		$app = \Lib\App::instance();

		// Log the message
		if ($this->logger) {
			$this->logger->error("URI '".$request->getUri()->getPath()."' not found");
		}

		if ($app->console) {
			echo "Error: request does not match any command::method or mandatory params are not properly set\n";
			return $response;
		}

		return $response
			->withStatus(404)
			->withHeader('Content-Type', 'text/html')
			->write($app->resolve('view')->render('error::404'));
	}

}