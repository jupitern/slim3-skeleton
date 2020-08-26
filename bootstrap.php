<?php

use Jupitern\Slim3\App;

error_reporting(E_ALL);
ini_set("display_errors", 1);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath(__DIR__ . '/') . DS);
define('APP_PATH', realpath(__DIR__ . '/app/') . DS);
define('CONFIG_PATH', realpath(__DIR__ . '/config/') . DS);
define('STORAGE_PATH', realpath(__DIR__ . '/storage/') . DS);
define('VIEWS_PATH', realpath(__DIR__ . '/views/') . DS);
define('PUBLIC_PATH', realpath(__DIR__ . '/public/') . DS);
define('LIB_PATH', realpath(__DIR__ . '/lib/') . DS);

require ROOT_PATH . 'vendor' . DS . 'autoload.php';

$settings = require CONFIG_PATH . 'app.php';
$cli = php_sapi_name() == 'cli';
$appName = $cli ? 'console' : 'http';

// change this line to load the environment as you please
if (file_exists(ROOT_PATH.'.env.staging')) $settings['env'] = App::STAGING;
if (file_exists(ROOT_PATH.'.env.production')) $settings['env'] = App::PRODUCTION;

if ($appName == 'console') {
    set_time_limit(0);
    ini_set('memory_limit','2000M');

    $cliCommandParts = (array)$GLOBALS['argv'];
    array_shift($cliCommandParts);

    if (in_array($cliCommandParts[0] ?? '', [App::DEVELOPMENT, App::STAGING, App::PRODUCTION])) {
        $settings['env'] = array_shift($cliCommandParts);
    }

    // Convert $argv to PATH_INFO and mock console environment
    $settings['slim']['environment'] = \Slim\Http\Environment::mock([
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
        'REQUEST_URI' => '/command'
    ]);

}

$settingsEnv = require CONFIG_PATH . ($settings['env']) . '.php';
$settings    = array_merge_recursive($settings, $settingsEnv);

// instance app
$app = app($appName, $settings);

$app->trace = new \Jupitern\Slim3\Utils\Logger(); // add into container for debug

// Set up dependencies
$app->registerProviders();
// Register middleware
$app->registerMiddleware();

if ($appName == 'console') {
    require CONFIG_PATH . 'routes' . DS . 'console.php';
    // .. add more route files here for console!
} else {
    require CONFIG_PATH . 'routes' . DS . 'http.php';
    // .. add more route files here for http!
}
