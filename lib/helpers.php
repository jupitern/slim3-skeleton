<?php

/**
 * get app instance
 *
 * @return \Lib\Framework\App
 */
function app($settings = [], $console = false)
{
	return \Lib\Framework\App::instance($settings, $console);
}


/**
 * @param string $url
 * @param null $showIndex
 * @param bool $includeBaseUrl
 *
 * @return string
 */
function url($url = '', $showIndex = null, $includeBaseUrl = true)
{
	return app()->url($url, $showIndex, $includeBaseUrl);
}

/**
 * output a variable, array or object
 *
 * @return void
 */
function debug($var, $exit = false)
{
	echo '<pre>';
	if (is_array($var)) {
		print_r($var);
	} else {
		var_dump($var);
	}
	echo '</pre>';
	if ($exit) exit();
}