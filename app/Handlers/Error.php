<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class Error extends \Slim\Handlers\Error
{
	protected $logger;

	public function __construct($displayErrorDetails, LoggerInterface $logger = null)
	{
		parent::__construct($displayErrorDetails);
		$this->logger = $logger;
	}

	public function __invoke(Request $request, Response $response, \Exception $exception)
	{

		$app = \Lib\Framework\App::instance();

		// Log the message
		if ($this->logger) {
			$this->logger->error($exception->getMessage()."\n".$exception->getTraceAsString());
		}

		if ($app->console) {
			return $app->sendResponse("Error: " . $exception->getMessage() . "\n\n" . $exception->getTraceAsString());
		}

		if (!$this->displayErrorDetails) {
			$resp = $app->resolve('view')->render('error::500', ['message' => $exception->getMessage()]);
			return $app->sendResponse($resp, 500);
		}

		return parent::__invoke($request, $response, $exception);
	}
}