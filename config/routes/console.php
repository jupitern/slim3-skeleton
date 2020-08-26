<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Jupitern\Slim3\App\Console\HelpCommand;

// automatic console command resolver
$app->get('/command', function (Request $request, Response $response, $args) use ($app, $cliCommandParts) {

    $response->withHeader('Content-Type', 'text/plain');

    if (empty($cliCommandParts)) {
        return $app->resolveRoute([HelpCommand::class, "show"], []);
    }

    for ($paramsStartPos=0; $paramsStartPos<count($cliCommandParts); $paramsStartPos++) {
        if (strpos($cliCommandParts[$paramsStartPos], '=') !== false) break;
    }

    $commandParts = array_slice($cliCommandParts, 0, $paramsStartPos);
    $paramsParts = array_slice($cliCommandParts, $paramsStartPos, count($cliCommandParts)-1);

    if (count($cliCommandParts) == 1) {
        return $app->resolveRoute([HelpCommand::class, 'show'], ['command' => $cliCommandParts[0]]);
    }
    elseif (count($commandParts) < 2) {
        return app()->notFound();
    }

    $method = array_pop($commandParts);
    $class = array_pop($commandParts);
    $namespace = "\\App\\Console". (count($commandParts) > 0 ? "\\".implode('\\', $commandParts) : "");

    $params = [];
    for ($i=0; $i<count($paramsParts); ++$i) {
        $parts = explode("=", $paramsParts[$i], 2);
        if (count($parts) != 2) return app()->notFound();

        $params[$parts[0]] = $parts[1];
    }

    return $app->resolveRoute([$namespace.'\\'.$class, $method], $params);
});