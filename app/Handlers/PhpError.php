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
		$app = app();

		// Log the message
		if ($this->logger) {
			$this->logger->critical($error->getMessage()."\n".$error->getTraceAsString());
		}

		if ($app->console) {
			return $response
				->withStatus(500)
				->withHeader('Content-type', 'text/plain')
				->write("Exception: {$error->getMessage()} \n\n {$error->getTraceAsString()}");
		}

		if ($this->determineContentType($request) == 'text/html') {
			if (!$this->displayErrorDetails) {
				$resp = $app->resolve('view')->render('error::500', ['message' => $error->getMessage()]);
				return $response->withStatus(500)->write($resp);
			}

			throw $error;
		}

		return parent::__invoke($request, $response, $error);
	}

	protected function renderJsonErrorMessage(\Throwable $error)
	{
		$error = ['message' => $error->getMessage()];

		if ($this->displayErrorDetails) {
			$error['exception'] = [];

			do {
				$error['exception'][] = [
					'type' => get_class($error),
					'code' => $error->getCode(),
					'message' => $error->getMessage(),
					'file' => $error->getFile(),
					'line' => $error->getLine(),
					'trace' => explode("\n", $error->getTraceAsString()),
				];
			} while ($error = $error->getPrevious());
		}

		return json_encode($error, JSON_PRETTY_PRINT);
	}
}