<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use League\Plates\Engine;

final class Error extends \Slim\Handlers\Error
{
	protected $logger;

	public function __construct($displayErrorDetails, Logger $logger = null)
	{
		parent::__construct($displayErrorDetails);
		$this->logger = $logger;
	}

	public function __invoke(Request $request, Response $response, \Exception $exception)
	{
		$app = app();

		// Log the message
		if ($this->logger) {
			$this->logger->error($exception->getMessage()."\n".$exception->getTraceAsString());
		}

		if ($app->console) {
			return $response
				->withStatus(500)
				->withHeader('Content-type', 'text/plain')
				->write("Exception: {$exception->getMessage()} \n\n {$exception->getTraceAsString()}");
		}

		if ($this->determineContentType($request) == 'text/html') {
			if (!$this->displayErrorDetails) {
				$resp = $app->resolve(Engine::class)->render('error::500', ['message' => $exception->getMessage()]);
				return $response->withStatus(500)->write($resp);
			}

			throw $exception;
		}

		return parent::__invoke($request, $response, $exception);
	}


	protected function renderJsonErrorMessage(\Exception $exception)
	{
		$error = ['message' => $exception->getMessage()];

		if ($this->displayErrorDetails) {
			$error['exception'] = [];

			do {
				$error['exception'][] = [
					'type' => get_class($exception),
					'code' => $exception->getCode(),
					'message' => $exception->getMessage(),
					'file' => $exception->getFile(),
					'line' => $exception->getLine(),
					'trace' => explode("\n", $exception->getTraceAsString()),
				];
			} while ($exception = $exception->getPrevious());
		}

		return json_encode($error, JSON_PRETTY_PRINT);
	}
}