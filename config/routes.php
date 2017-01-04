<?php

// example route to resolve request to uri '/' to \\App\\Http\\Site\\Welcome::index
$app->any('/', function ($request, $response, $args) use($app) {
	$app->resolveRoute(['class' => 'welcome', 'method' => 'index'], "\\App\\Http\\Site");
});

// example route to resolve request to that matches '/{class}/{method}'
// resolveRoute will try to find a corresponding class::method in a given namespace
$app->any('/{class}/{method}', function ($request, $response, $args) use($app) {
	$app->resolveRoute($args, "\\App\\Http\\Site");
});
