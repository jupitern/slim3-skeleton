<?php namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Stats
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		$app = \Lib\Framework\App::instance();
		$startTime = microtime(true);

		$response = $next($request, $response);

		$logger = $app->resolve('logger');
		$db = $app->resolve('database');

		$logger->debug(
			"Execution time: ".number_format((microtime(true) - $startTime), 2)." seg\n".
			"Memory Usage: ".round(memory_get_usage() / 1048576,2).' Mb'
		);

		$queryLog = '';
		foreach ($db->getQueryLog() as $query) {
			$bindings = '';
			foreach ($query['bindings'] as $key => $value) {
				$bindings .= ":$key = $value; ";
			}
			$queryLog .= "\n".$query['query'];
			if (!empty($bindings)) {
				$queryLog .= "\n$bindings";
			}
			$queryLog .= "\nquery time: ".$query['time']." ms\n";
		}
		$logger->debug($queryLog);

		return $response;
	}

}