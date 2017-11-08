<?php

namespace Lib\Framework;
use Psr\Http\Message\ResponseInterface;

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
			return new \App\Handlers\Error($displayErrorDetails, $this->resolve('logger'));
		};
		$this->getContainer()['phpErrorHandler'] = function($c) use($loggerName, $displayErrorDetails) {
			return new \App\Handlers\PhpError($displayErrorDetails, $this->resolve('logger'));
		};
		$this->getContainer()['notFoundHandler'] = function($c) use($loggerName, $displayErrorDetails) {
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

	//proxy calls to slim
	public function __call($fn, $args=[])
	{
		if (method_exists($this->slim,$fn)) {
			return call_user_func_array([$this->slim,$fn] , $args);
		}
		throw new \Exception('Method not found :: '.$fn);
	}

	//proxy all sets to slim
	public function __set($k, $v)
	{
		$this->slim->{$k} = $v;
	}

	//proxy all gets to slim __get($k)
	public function __get($k)
	{
		return $this->slim->{$k};
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
			return is_callable($container[$name]) ? call_user_func_array($container[$name], $params) : $container[$name];
		}

		if (class_exists($name)) {
			$reflector = new \ReflectionClass($name);

			if ($reflector->isInstantiable()) {
				$constructor = $reflector->getConstructor();

				if ($constructor === null) {
					return new $name;
				} else {
					$dependencies = $this->resolveDependencies($constructor->getParameters(), $params);
					return $reflector->newInstanceArgs($dependencies);
				}
			}
		}

		return null;
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
	public function resolveRoute($namespace = "\\App\\Http", $className, $methodName, $requestParams = [])
	{
		$class = new \ReflectionClass($namespace.'\\'.$className);

		if (!$class->isInstantiable() || !$class->hasMethod($methodName)) {
			$handler = $this->getContainer()['notFoundHandler'];
			return $handler($this->getContainer()['request'], $this->getContainer()['response']);
		}

		$method = $class->getMethod($methodName);
		$constructorArgs = $this->resolveDependencies($class->getConstructor()->getParameters());
		$methodArgs = $this->resolveDependencies($method->getParameters(), $requestParams);
		$ret = $method->invokeArgs($class->newInstanceArgs($constructorArgs), $methodArgs);

		return $this->sendResponse($ret);
	}


	/**
	 * resolve a list of dependencies for a given method parameters
	 *
	 * @param array $params
	 * @param array $values
	 * @return array
	 */
	private function resolveDependencies(array $params = [], array $values = [])
	{
		$dependencies = [];
		foreach ($params as $param) {

			if (array_key_exists($param->getName(), $values)) {
				$dependencies[] = $values[$param->getName()];
			} else {
				$dependencyName = !empty($param->getClass()) ? $param->getClass()->getName() : $param->getName();

				$dependency = $this->resolve($dependencyName, $values);

				if ($dependency !== null) {
					$dependencies[] = $dependency;
				} elseif ($param->isDefaultValueAvailable()) {
					$dependencies[] = $param->getDefaultValue();
				} else {
					throw new \Exception("Error resolving method dependencies for param {$param->getName()}");
				}
			}
		}

		return $dependencies;
	}

}