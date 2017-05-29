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
		$app = \Lib\Framework\App::instance();

		// Log the message
		if ($this->logger) {
			$this->logger->critical($error->getMessage()."\n".$error->getTraceAsString());
		}

		if ($app->console) {
			$response = $app->sendResponse("Error: " . $error->getMessage() . "\n\n" . $error->getTraceAsString());
		}

		if (!$this->displayErrorDetails) {
			$resp = $app->resolve('view')->render('error::500', ['message' => $error->getMessage()]);
			return $app->sendResponse($resp, 500);
		}

		return parent::__invoke($request, $response, $error);
	}
}