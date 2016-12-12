<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

final class PhpError extends \Slim\Handlers\PhpError
{
	protected $logger;

	public function __construct($displayErrorDetails, Logger $logger = null)
	{
		parent::__construct($displayErrorDetails);
		$this->logger = $logger;
	}

	public function __invoke(Request $request, Response $response, \Throwable $error)
	{
		$app = \App\App::instance();

		// Log the message
		if ($this->logger) {
			$this->logger->critical($error->getMessage()."\n".$error->getTraceAsString());
		}

		if (\App\App::instance()->console) {
			echo "Error: ".$error->getMessage()."\n\n";
			echo $error->getTraceAsString();
			return $response;
		}

		if (!$this->displayErrorDetails) {
			return $response
				->withStatus(500)
				->withHeader('Content-Type', 'text/html')
				->write($app->resolve('view')->render('error::500'));
		}

		return parent::__invoke($request, $response, $error);
	}
}