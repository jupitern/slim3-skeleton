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
			$this->logger->error("URI '".$request->getUri()->getPath()."' not found. IP: ".$this->getClientIP());
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

	// Function to get the client IP address
	private function getClientIP() {
		$ipAddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipAddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipAddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipAddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipAddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$ipAddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipAddress = getenv('REMOTE_ADDR');
		else
			$ipAddress = 'UNKNOWN';
		return $ipAddress;
	}

}