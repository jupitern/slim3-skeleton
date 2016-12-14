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
		$app = \App\App::instance();

		// Log the message
		if ($this->logger) {
			$this->logger->error($exception->getMessage()."\n".$exception->getTraceAsString());
		}

		if ($app->console) {
			echo "Error: ".$exception->getMessage()."\n\n";
			echo $exception->getTraceAsString();
			return $response;
		}

		if (!$this->displayErrorDetails) {
			return $response
				->withStatus(500)
				->withHeader('Content-Type', 'text/html')
				->write($app->resolve('view')->render('error::500', ['message' => $exception->getMessage()]));
		}

		if (isset($app->getContainer()['whoops'])) {
			$app->getContainer()->get('whoops')->handleException($exception);
		}

		return parent::__invoke($request, $response, $exception);
	}
}