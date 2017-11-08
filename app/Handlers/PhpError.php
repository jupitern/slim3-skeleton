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