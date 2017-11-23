<?php

namespace Lib\Framework;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class App
{
	public $console = false;

	const DEVELOPMENT = 'development';
	const STAGING = 'staging';
	const PRODUCTION = 'production';
	public static $env = self::DEVELOPMENT;

	/** @var \Slim\App */
	private $slim = null;
	private $settings = [];
	private static $instance = null;


	protected function __construct($settings, $console = false)
	{
		$this->settings = $settings;
		$this->console = $console;
		$this->slim = new \Slim\App($settings);
		$displayErrorDetails = $settings['settings']['debug'];

		date_default_timezone_set($settings['settings']['timezone']);

		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			if (!($errno & error_reporting())) {
				return;
			}
			throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
		});

		$loggerName = $this->console ? 'console' : 'app';

		$this->getContainer()['errorHandler'] = function($c) use($loggerName, $displayErrorDetails) {
			return new \App\Handlers\Error($displayErrorDetails, $this->resolve(LoggerInterface::class));
		};
		$this->getContainer()['phpErrorHandler'] = function($c) use($loggerName, $displayErrorDetails) {
			return new \App\Handlers\PhpError($displayErrorDetails, $this->resolve(LoggerInterface::class));
		};
		$this->getContainer()['notFoundHandler'] = function($c) use($loggerName, $displayErrorDetails) {
			return new \App\Handlers\NotFound($this->resolve(LoggerInterface::class));
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
	 * set configuration param
	 *
	 * @param string $param
	 * @param mixed $value
	 */
	public function setConfig($param, $value)
	{
		$dn = new \Lib\Utils\DotNotation($this->settings);
		$dn->set($param, $value);
	}

	/**
	 * get configuration param
	 *
	 * @param string $param
	 * @param string $defaultValue
	 * @return mixed
	 */
	public function getConfig($param, $defaultValue = null)
	{
		$dn = new \Lib\Utils\DotNotation($this->settings);
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


	//proxy all gets to slim
	public function __get($name)
	{
		$c = $this->getContainer();

		if ($c->has($name)) {
			return $c->get($name);
		}
		return $this->resolve($name);
	}

	//proxy all sets to slim
	public function __set($k, $v)
	{
		$this->slim->{$k} = $v;
	}

	// proxy calls to slim
	public function __call($fn, $args = [])
	{
		if (method_exists($this->slim, $fn)) {
			return call_user_func_array([$this->slim,$fn], $args);
		}
		throw new \Exception('Method not found :: '.$fn);
	}


	/**
	 * generate a url
	 *
	 * @param string $url
	 * @param boolean $showIndex pass null to assume config file value
	 * @param boolean $includeBaseUrl
	 * @return string
	 */
	public function url($url = '', $showIndex = null, $includeBaseUrl = true)
	{
		$baseUrl = $includeBaseUrl ? $this->getConfig('settings.baseUrl') : '';

		$indexFile = '';
		if ($showIndex === null && (bool)$this->getConfig('settings.indexFile')) {
			$indexFile = 'index.php/';
		}
		if (strlen($url) > 0 && $url[0] == '/') {
			$url = ltrim($url, '/');
		}

		return $baseUrl.$indexFile.$url;
	}

	/**
	 * return a response object
	 *
	 * @param mixed $resp
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function sendResponse($resp)
	{
		$response = $this->resolve('response');

		if ($resp instanceof ResponseInterface) {
			$response = $resp;
		}  elseif (is_array($resp) || is_object($resp)) {
			$response->withJson($resp);
		} else {
			$response->write($resp);
		}

		return $response;
	}


	/**
	 * resolve and call a given class / method
	 *
	 * @param string $namespace
	 * @param string $className
	 * @param string $methodName
	 * @param array $requestParams
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function resolveRoute($namespace = '\App\Http', $className, $methodName, $requestParams = [])
	{
		$class = new \ReflectionClass($namespace.'\\'.$className);

		if (!$class->isInstantiable() || !$class->hasMethod($methodName)) {
			$handler = $this->getContainer()['notFoundHandler'];
			return $handler($this->getContainer()['request'], $this->getContainer()['response']);
		}

		$constructorArgs = $this->resolveMethodDependencies($class->getConstructor());

		$method = $class->getMethod($methodName);
		$ret = $method->invokeArgs(
			$class->newInstanceArgs($constructorArgs),
			$this->resolveMethodDependencies($method, $requestParams)
		);

		return $this->sendResponse($ret);
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
		//echo "auto-resolving {$name}<br/>";

		$c = $this->getContainer();
		if ($c->has($name)) {
			return is_callable($c[$name]) ? call_user_func_array($c[$name], $params) : $c[$name];
		}

		if (!class_exists($name)) {
			throw new \ReflectionException("Unable to resolve {$name}");
		}

		$reflector = new \ReflectionClass($name);

		if (!$reflector->isInstantiable()) {
			throw new \ReflectionException("Class {$name} is not instantiable");
		}

		if ($constructor = $reflector->getConstructor()) {
			$dependencies = $this->resolveMethodDependencies($constructor);
			return $reflector->newInstanceArgs($dependencies);
		}

		return new $name();
	}


	/**
	 * resolve dependencies for a given class method
	 *
	 * @param \ReflectionMethod $method
	 * @param array $urlParams
	 * @return array
	 */
	private function resolveMethodDependencies(\ReflectionMethod $method, $urlParams = [])
	{
		return array_map(function ($dependency) use($urlParams) {
			return $this->resolveDependency($dependency, $urlParams);
		}, $method->getParameters());
	}


	/**
	 * resolve a dependency parameter
	 *
	 * @param \ReflectionParameter $param
	 * @return mixed
	 */
	private function resolveDependency(\ReflectionParameter $param, $urlParams = [])
	{
//		echo "revolve param {$param->getName()}<br/>";

		// for controller method para injection from $_GET
		if (count($urlParams) && array_key_exists($param->name, $urlParams)) {
			return $urlParams[$param->name];
		}

		// param is instantiable
		if ($param->isDefaultValueAvailable()) {
			return $param->getDefaultValue();
		}

		if (!$param->getClass()) {
			throw new \ReflectionException("Unable to resolve method param {$param->name}");
		}

		// try to resolve from container
		return $this->resolve($param->getClass()->name);
	}

}