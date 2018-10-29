<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath(__DIR__.'/').DS);
define('APP_PATH', realpath(__DIR__.'/app/').DS);
define('CONFIG_PATH', realpath(__DIR__.'/config/').DS);
define('STORAGE_PATH', realpath(__DIR__.'/storage/').DS);
define('RESOURCES_PATH', realpath(__DIR__.'/resources/').DS);
define('PUBLIC_PATH', realpath(__DIR__.'/public/').DS);
define('LIB_PATH', realpath(__DIR__.'/lib/').DS);

require ROOT_PATH.'vendor'.DS.'autoload.php';

$appName = php_sapi_name() == 'cli' ? 'console' : 'http';

$settings = require CONFIG_PATH.'app.php';
$settingsEnv = require CONFIG_PATH.($settings['settings']['env']).'.php';
$settings = array_merge_recursive($settings, $settingsEnv);

if ($appName == 'console') {

	set_time_limit(0);
	$argv = $GLOBALS['argv'];
	array_shift($argv);

    // Convert $argv to PATH_INFO and mock console environment
    $settings['environment'] = \Slim\Http\Environment::mock([
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
        'REQUEST_URI' => count($argv) >= 2 ? "/{$argv[0]}/{$argv[1]}" : "/help"
    ]);
}

// instance app
$app = app($appName, $settings);
// Set up dependencies
$app->registerProviders();
// Register middleware
$app->registerMiddleware();

if ($appName == 'console') {
	// include your routes for cli requests here
	require CONFIG_PATH.'routes'.DS.'console.php';
}
else {
	// include your routes for http requests here
	require CONFIG_PATH.'routes'.DS.'app.php';
}

$app->run();