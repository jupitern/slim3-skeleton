<?php

namespace Lib;
use Slim\Exception\NotFoundException;

class App
{

	public $console = false;

	private $slim = null;
	private $settings = [];
	private static $instance = null;


	protected function __construct($settings, $console = false)
	{
		$displayErrorDetails = $settings['settings']['displayErrorDetails'];
		$this->settings = $settings;
		$this->console = $console;
		$this->slim = new \Slim\App($this->settings);

		date_default_timezone_set($this->settings['settings']['timezone']);

		set_error_handler(function ($errno, $errstr, $errfile, $errline) {
			if (!($errno & error_reporting())) {
				return;
			}
			throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
		});

		$loggerName = $this->console ? 'console' : 'app';

		$this->getContainer()['errorHandler'] = function ($c) use($loggerName, $displayErrorDetails) {
			return new \App\Handlers\Error($displayErrorDetails, $this->resolve('logger'));
		};
		$this->getContainer()['phpErrorHandler'] = function ($c) use($loggerName, $displayErrorDetails) {
			return new \App\Handlers\PhpError($displayErrorDetails, $this->resolve('logger'));
		};
		$this->getContainer()['notFoundHandler'] = function ($c) use($loggerName, $displayErrorDetails) {
			return new \App\Handlers\NotFound($this->resolve('logger'));
		};
	}

	/**
	 * Application Singleton Factory
	 *
	 * @return static
	 */
	final public static function instance($settings = [], $console = false)
	{
		if (null === static::$instance) {
			static::$instance = new static($settings, $console);
		}

		return static::$instance;
	}


	/**
	 * set configuration param
	 *
	 * @return \Interop\Container\ContainerInterface
	 */
	public function getContainer()
	{
		return $this->slim->getContainer();
	}

	/**
	 * resolve a dependency from the container
	 *
	 * @param string $name
	 * @param string $params
	 * @param mixed
	 */
	public function resolve($name, $params = [])
	{
		$container = $this->getContainer();
		if ($container->has($name)) {
			if (is_callable($container[$name])) {
				return call_user_func_array($container[$name], $params);
			}
			else {
				return $container[$name];
			}
		}
		return null;
	}

	/**
	 * set configuration param
	 *
	 * @param array $config
	 */
	public function setConfig( $param, $value )
	{
		$dn = new \Lib\DotNotation($this->settings);
		$dn->set($param, $value);
	}
	
	/**
	 * get configuration param
	 *
	 * @param string $param
	 * @param string $defaultValue
	 * @return mixed
	 */
	public function getConfig( $param, $defaultValue = null )
	{
		$dn = new \Lib\DotNotation($this->settings);
		return $dn->get($param, $defaultValue);
	}

	/**
	 * register providers
	 */
	public function registerProviders()
	{
		foreach ($this->getConfig('providers') as $provider) {
			$this->slim->add(new $provider($this->getContainer()));
		}
	}

	/**
	 * register providers
	 */
	public function registerMiddleware()
	{
		foreach ($this->getConfig('middleware') as $middleware) {
			$this->slim->add(new $middleware);
		}
	}

	//proxy calls to slim
	public function __call($fn,$args=[])
	{
		if (method_exists($this->slim,$fn)) {
			return call_user_func_array([$this->slim,$fn] , $args);
		}
		throw new \Exception('Method not found :: '.$fn);
	}

	//proxy all sets to slim
	public function __set($k,$v)
	{
		$this->slim->{$k} = $v;
	}

	//proxy all gets to slim __get($k)
	public function __get($k)
	{
		return $this->slim->{$k};
	}

	/**
	 * retrieve base url
	 *
	 * @param $url
	 * @param $includeBaseUrl
	 * @return string
	 */
	public function baseUrl()
	{
		return $this->getConfig('settings.baseUrl');
	}

	/**
	 * generate a url
	 *
	 * @param $url
	 * @param $includeBaseUrl
	 * @return string
	 */
	public function url($url = '', $includeBaseUrl = true)
	{
		$baseUrl = $includeBaseUrl ? $this->getConfig('settings.baseUrl') : '';
		$indexFile = (bool)$this->getConfig('settings.indexFile') ? 'index.php/' : '';
		if (strlen($url) > 0 && $url[0] == '/') $url = ltrim($url, '/');

		return $baseUrl.$indexFile.$url;
	}

	/**
	 * debug helper
	 *
	 * @return static
	 */
	public static function debug($var)
	{
		echo '<pre>';
		if (is_array($var)) {
			print_r($var);
		} else {
			var_dump ($var);
		}
		echo '</pre>';
	}


	/**
	 * resolve and call a given class / method
	 * $args = ['class' => 'Test', 'method' => 'test']
	 *
	 * @param $args
	 * @param $baseNamespace
	 */
	public function resolveRoute($args, $baseNamespace = "\\App\\Http")
	{
		$classParts = array_slice($args, 0, array_search('method', array_keys($args)));
		$params = isset($args['params']) ?
			$args['params'] : array_slice($args, array_search('method', array_keys($args)) + 1, count($args));

		$className = $baseNamespace;
		foreach ($classParts as $part) {
			$className .= "\\" . ucfirst($part);
		}

		try {
			if (!isset($args['method']) || !method_exists($className, $args['method'])) {
				throw new \BadMethodCallException();
			}
			else {
				$class = new \ReflectionClass($className);
				$constructor = $class->getConstructor();
				$method = $class->getMethod($args['method']);

				$constructorArgs = $constructor !== null ? $this->resolveDependencies($class, $constructor, $params) : [];
				$methodArgs = $this->resolveDependencies($class, $method, $params);

				$controllerObj = $class->newInstanceArgs($constructorArgs);
				$method->invokeArgs($controllerObj, $methodArgs);
			}
		}
		catch (\BadMethodCallException $e) {
			$handler = $this->getContainer()['notFoundHandler'];
			$handler($this->getContainer()['request'], $this->getContainer()['response']);
		}
	}


	/**
	 * resolve dependencies to inject searching in order (container, method, default value)
	 *
	 * @param \ReflectionClass $className
	 * @param \ReflectionMethod $methodName
	 * @param array $requestParams
	 */
	private function resolveDependencies(\ReflectionClass $class, \ReflectionMethod $method = null, $requestParams = [])
	{
		$methodArgs = [];
		foreach ($method->getParameters() as $param) {
			$dependency = $this->resolve($param->getName());
			if ($dependency) {
				$methodArgs[] = $dependency;
			}
			elseif (array_key_exists($param->getName(), $requestParams)) {
				$methodArgs[] = $requestParams[$param->getName()];
			}
			elseif ($param->isDefaultValueAvailable()) {
				$methodArgs[] = $param->getDefaultValue();
			}
			else {
				throw new \BadMethodCallException();
			}
		}
		return $methodArgs;
	}

}