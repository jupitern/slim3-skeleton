<?php

$app->any('/', function ($request, $response, $args) use($app) {
	$app->resolveRoute(['class' => 'welcome', 'method' => 'index'], "\\App\\Http\\Site");
});

$app->any('/{class}/{method}', function ($request, $response, $args) use($app) {
	$app->resolveRoute($args, "\\App\\Http\\Site");
});
