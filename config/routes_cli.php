<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// automatic console command resolver
$app->get('/{command}/{method}', function (Request $request, Response $response, $args) use ($app, $argv) {
	$parts = array_chunk($argv, 2);

	$params = [];
	if (isset($parts[1])) {
		foreach ((array)$parts[1] as $param) {
			$arr = explode('=', $param);
			$params[$arr[0]] = $arr[1];
		}
	}

	$response->withHeader('Content-Type', 'text/plain');

	return $app->resolveRoute("\\App\\Console", $parts[0][0], $parts[0][1], $params);
});

// help route to display available command in
$app->get('/help', function (Request $request, Response $response, $args) {
	$response->withHeader('Content-Type', 'text/plain');

	$response->write("\n** Slim command line **\n\n");
	$response->write("usage: php ".ROOT_PATH."cli.php <command-name> <method-name> [parameters...]\n\n");
	$response->write("The following commands are available:\n");

	$iterator = new DirectoryIterator(APP_PATH.'Console');
	foreach ($iterator as $fileinfo) {
		if ($fileinfo->isFile()) {
			$className = str_replace(".php", "", $fileinfo->getFilename());
			$class = new \ReflectionClass("\\App\\Console\\$className");

			if (!$class->isAbstract()) {
				$response->write("- ".strtolower($className)."\n");

				foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
					if (strpos($method->getName(), '__') === 0) {
						break;
					}
					$response->write("       ".strtolower($method->getName())." ");
					foreach ($method->getParameters() as $parameter) {
						if ($parameter->isDefaultValueAvailable()) {
							$response->write("[".$parameter->getName()."=value] ");
						}
						else {
							$response->write($parameter->getName()."=value ");
						}
					}
					$response->write("\n");
				}
				$response->write("\n");
			}
		}
	}

	return $response;
});