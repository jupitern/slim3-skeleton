<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// example route to resolve request to uri '/' to \\App\\Http\\Site\\Welcome::index
$app->any('/', function(Request $request, Response $response, $args) use($app) {
	return $app->resolveRoute('\App\Http\Site', 'Welcome', 'index', $args);
});

$app->get('/hello/{name}', function(Request $request, Response $response, $args) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});

// example route to resolve request to that matches '/{class}/{method}'
// resolveRoute will try to find a corresponding class::method in a given namespace
$app->any('/{class}/{method}', function(Request $request, Response $response, $args) use($app) {
	return $app->resolveRoute('\App\Http\Site', $args['class'], $args['method'], $args);
});

/*API ROUTE*/
$app->any('/api/v1/{module}/{class}/{method}', function (Request $request, Response $response, $args) use ($app) {
    $nameSpace = "\App\Http\Api\V1\\" . ucfirst($args['module']);
    $method = $args['method'] . "Action";
    $class = ucfirst($args['class']);
    return $app->resolveRoute($nameSpace, $class, $method, $args);
});