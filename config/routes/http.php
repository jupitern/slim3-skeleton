<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response, $args) use($app) {
    return $app->resolveRoute([\App\Http\Welcome::class, "index"], $args);
});


// route example with optional named placeholder
$app->get('/welcome[/{name}]', function(Request $request, Response $response, $args) use($app) {
    return $app->resolveRoute([\App\Http\Welcome::class, "index"], $args);
});


// example calling http://localhost:8080/index.php/test/nuno with the route bellow
// injects the :name param value into the method $name parameter
// Other parameters in the method will be searched in the container by classname or automatically resolved
// in this example the resolveRoute method will create a user instance and inject it in the controller method
$app->any('/test[/{name}]', function ($request, $response, $args) use($app) {
    return $app->resolveRoute([\App\Http\Welcome::class, "method"], $args);
});


$app->any('/tests', function ($request, $response, $args) use($app) {
    return $app->resolveRoute([\App\Http\Welcome::class, "tests"], $args);
});