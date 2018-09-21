<?php
/**
 * get app instance
 * @param array $settings
 * @param boolean $console
 *
 * @return \Lib\Framework\App
 */
function app($settings = [], $console = false)
{
	return \Lib\Framework\App::instance($settings, $console);
}


/**
 * @param string $url
 * @param boolean|null $showIndex pass null to assume config default value
 * @param boolean $includeBaseUrl
 *
 * @return string
 */
function url($url = '', $showIndex = null, $includeBaseUrl = true)
{
	return app()->url($url, $showIndex, $includeBaseUrl);
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
 * output a variable, array or object
 *
 * @param mixed $var
 * @param boolean $exit
 * @param boolean $return
 * @param string $separator|null
 *
 * @return string
 */
function debug($var, $exit = false, $return = false, $separator = null)
{
	$log = "";
	if ($separator == null) $separator = app()->console ? "\n" : "<br/>";

	if ($separator == "<br/>") $log .= '<pre>';
	if (is_array($var)) {
		$log .= print_r($var, true);
	} elseif (is_object($var)) {
		ob_start();
		var_dump($var);
		$log .= ob_get_clean();
	} else {
		$log .= $var;
	}

	if ($separator == "<br/>") $log .= '</pre>';

	if (!$return) echo $log.$separator;
	if ($exit) exit();

	return $log.$separator;
}

/**
 * output the database query log
 *
 * @param boolean $exit
 * @param boolean $return
 * @param string $separator
 *
 * @return string
 */
function dbLog($exit = false, $return = false, $separator = null)
{
	$app = app();
	$log = "";
	$queryLog = $app->resolve(\Illuminate\Database\ConnectionInterface::class)->getQueryLog();

	if ($separator == null) $separator = $app->console ? "\n" : "<br/>";

	if (!empty($queryLog)) {
		$log = "QUERY LOG (".count($queryLog)." queries):{$separator}{$separator}";

		foreach ($queryLog as $ql) {

			if (strpos($ql['query'], "select * from SchemaTableColumns") !== false) continue;

			foreach ($ql['bindings'] as $binding) {
				$ql['query'] = preg_replace('/ \?/', " '{$binding}'", $ql['query'], 1);
			}

			$log .= $ql['query'].$separator;
			$log .= "Execution time: ".$ql['time']."ms".$separator;
			$log .= $separator;
		}
	}

	if (!$return) echo $log;
	if ($exit) exit();

	return $log;
}


/**
 * output the database query log
 *
 * @param boolean $exit
 * @param boolean $return
 * @param string $separator
 *
 * @return string
 */
function dbLastQuery($exit = false, $return = false, $separator = null)
{
	$app = app();
	$log = "";
	$queryLog = $app->resolve(\Illuminate\Database\ConnectionInterface::class)->getQueryLog();

	if ($separator == null) $separator = $app->console ? "\n" : "<br/>";

	for ($i = count($queryLog)-1; $i >= 0; $i--) {
		$ql = $queryLog[$i];
		if (strpos($ql['query'], "select * from SchemaTableColumns") !== false) continue;

		foreach ($ql['bindings'] as $binding) {
			$ql['query'] = preg_replace('/ \?/', " '{$binding}'", $ql['query'], 1);
		}

		$log .= $ql['query'].$separator;
		$log .= "Execution time: ".$ql['time']."ms".$separator;

		break;
	}

	if (!$return) echo $log;
	if ($exit) exit();

	return $log;
}