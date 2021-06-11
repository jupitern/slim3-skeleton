<?php

/**
 * get app instance
 *
 * @param string $appName
 * @param array  $configs
 *
 * @return \Jupitern\Slim3\App
 */
function app($appName = null, $configs = [])
{
    return \Jupitern\Slim3\App::instance($appName, $configs);
}


/**
 * log a message
 *
 * @param mixed     $level
 * @param mixed     $message
 * @param array     $context
 *
 * @throws ReflectionException
 */
function addLog($level, $message, array $context = [])
{
    if (!app()->has('logger')) return;

    if (is_object($message) || is_array($message)) {
        $message = json_encode($message);
    }

    /** @var \Psr\Log\LoggerInterface $logger */
    $logger = app()->resolve('logger');
    $logger->log($level, $message, $context);
}


/**
 * add a message for trace
 *
 * @param mixed     $message
 * @throws ReflectionException
 */
function addTrace($message)
{
    if (!app()->has('trace')) return;

    if (is_object($message) || is_array($message)) {
        $message = json_encode($message);
    }

    app()->resolve('trace')->add($message);
}


/**
 * output a message in console. optionally added to the trace log.
 *
 * @param mixed     $message
 * @param bool      $trace
 * @throws ReflectionException
 */
function output($message, bool $trace = true)
{
    if (!app()->isConsole()) return;

    if (is_object($message) || is_array($message)) {
        $message = json_encode($message);
    }

    if ($trace && app()->has('trace')) {
        app()->resolve('trace')->add($message);
    }

    echo $message.PHP_EOL;
}


/**
 * @param boolean|null $showIndex pass null to assume config default value
 *
 * @return string
 */
function baseUrl($showIndex = null)
{
    return app()->url('', $showIndex);
}


/**
 * @param string       $url
 * @param boolean|null $showIndex pass null to assume config default value
 * @param boolean      $includeBaseUrl
 *
 * @return string
 */
function url($url = '', $showIndex = null, $includeBaseUrl = true)
{
    return app()->url($url, $showIndex, $includeBaseUrl);
}


/**
 * @param string $filename
 * @param string $container
 *
 * @return string
 */
function imgUrl($filename = '', $container = '')
{
    $baseUrl   = app()->getConfig('services.fs_s3.settings.baseUrl');
    $container = app()->getConfig('services.fs_s3.settings.containerPrefix') . "/" . $container;

    return $baseUrl . $container . '/' . $filename;
}


/**
 * output a variable, array or object
 *
 * @param mixed   $var
 * @param boolean $exit
 * @param boolean $return
 * @param string  $separator |null
 *
 * @return string
 */
function debug($var, $exit = false, $return = false, $separator = null)
{
    $log = "";
    if ($separator == null) {
        $separator = php_sapi_name() == 'cli' ? "\n" : "<br/>";
    }

    if ($separator == "<br/>") {
        $log .= '<pre>';
    }
    if (is_array($var)) {
        $log .= print_r($var, true);
    } elseif (is_object($var)) {
        ob_start();
        var_dump($var);
        $log .= ob_get_clean();
    } else {
        $log .= $var;
    }

    if ($separator == "<br/>") {
        $log .= '</pre>';
    }

    if (!$return) {
        echo $log . $separator;
    }
    if ($exit) {
        exit();
    }

    return $log . $separator;
}
