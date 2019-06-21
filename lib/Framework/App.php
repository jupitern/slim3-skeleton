<?php

namespace Lib\Framework;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Handlers\Error;
use App\Handlers\PhpError;
use App\Handlers\NotFound;
use Lib\Utils\DotNotation;

class App
{

    public $appName;

    const DEVELOPMENT = 'development';
    const STAGING = 'staging';
    const PRODUCTION = 'production';
    public $env = self::DEVELOPMENT;

    /** @var \Slim\App */
    private $slim = null;
    private $settings = [];
    private static $instance = null;


    /**
     * @param string $appName
     * @param array $settings
     */
    protected function __construct($appName = '', $settings = [])
    {
        $this->appName = $appName;
        $this->settings = $settings;
        $this->slim = new \Slim\App($settings);
        $this->env = $settings['settings']['env'];
        $container = $this->getContainer();
        $displayErrorDetails = $settings['settings']['debug'];

        date_default_timezone_set($settings['settings']['timezone']);
        \Locale::setDefault($settings['settings']['locale']);

        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            if (!($errno & error_reporting())) {
                return;
            }
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        $container[RequestInterface::class] = $container['request'];
        $container[ResponseInterface::class] = $container['response'];

        $container['errorHandler'] = function() use($displayErrorDetails) {
            return new Error($displayErrorDetails);
        };
        $container['phpErrorHandler'] = function() use($displayErrorDetails) {
            return new PhpError($displayErrorDetails);
        };
        $container['notFoundHandler'] = function() {
            return new NotFound();
        };
    }

    /**
     * Application Singleton Factory
     *
     * @param string $appName
     * @param array $settings
     * @return static
     */
    final public static function instance($appName = '', $settings = [])
    {
        if (null === static::$instance) {
            static::$instance = new static($appName, $settings);
        }

        return static::$instance;
    }


    /**
     * get if running application is console
     *
     * @return boolean
     */
    public function isConsole()
    {
        return php_sapi_name() == 'cli';
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
        $dn = new DotNotation($this->settings);
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
        $dn = new DotNotation($this->settings);
        return $dn->get($param, $defaultValue);
    }


    /**
     * register providers
     *
     * @return void
     */
    public function registerProviders()
    {
        $providers = (array)$this->getConfig('providers');
        array_walk($providers, function(&$appName, $provider) {
            if (strpos($appName, $this->appName) !== false) {
                /** @var $provider \App\ServiceProviders\ProviderInterface */
                $provider::register();
            }
        });
    }


    /**
     * register providers
     *
     * @return void
     */
    public function registerMiddleware()
    {
        $middlewares = array_reverse((array)$this->getConfig('middleware'));
        array_walk($middlewares, function($appName, $middleware) {
            if (strpos($appName, $this->appName) !== false) {
                $this->slim->add(new $middleware);
            }
        });
    }


    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        $c = $this->getContainer();
        return $c->has($name);
    }


    /**
     * @param $name
     * @return mixed
     * @throws \ReflectionException
     */
    public function __get($name)
    {
        $c = $this->getContainer();

        if ($c->has($name)) {
            return $c->get($name);
        }
        return $this->resolve($name);
    }


    /**
     * @param $k
     * @param $v
     */
    public function __set($k, $v)
    {
        $this->slim->{$k} = $v;
    }


    /**
     * @param $fn
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($fn, $args = [])
    {
        if (method_exists($this->slim, $fn)) {
            return call_user_func_array([$this->slim, $fn], $args);
        }
        throw new \Exception('Method not found :: '.$fn);
    }


    /**
     * generate a url
     *
     * @param string $url
     * @param boolean|null $showIndex pass null to assume config file value
     * @param boolean $includeBaseUrl
     * @return string
     */
    public function url($url = '', $showIndex = null, $includeBaseUrl = true)
    {
        $baseUrl = $includeBaseUrl ? $this->getConfig('settings.baseUrl') : '';

        $indexFile = '';
        if ($showIndex || ($showIndex === null && (bool)$this->getConfig('settings.indexFile'))) {
            $indexFile = 'index.php/';
        }
        if (strlen($url) > 0 && $url[0] == '/') {
            $url = ltrim($url, '/');
        }

        return strtolower($baseUrl.$indexFile.$url);
    }


    /**
     * return a response object
     *
     * @param mixed $resp
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \ReflectionException
     */
    public function sendResponse($resp)
    {
        $response = $this->resolve('response');

        if ($resp instanceof ResponseInterface) {
            $response = $resp;
        } elseif (is_array($resp) || is_object($resp)) {
            $response = $response->withJson($resp);
        } else {
            $response = $response->write($resp);
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
     * @throws \ReflectionException
     */
    public function resolveRoute($namespace = '\App\Http', $className, $methodName, $requestParams = [])
    {

        try {
            $class = new \ReflectionClass($namespace.'\\'.$className);

            if (!$class->isInstantiable() || !$class->hasMethod($methodName)) {
                throw new \ReflectionException("route class is not instantiable or method does not exist");
            }
        } catch (\ReflectionException $e) {
            return $this->notFound();
        }

        $constructorArgs = $this->resolveMethodDependencies($class->getConstructor());
        $controller = $class->newInstanceArgs($constructorArgs);

        $method = $class->getMethod($methodName);
        $args = $this->resolveMethodDependencies($method, $requestParams);

        $ret = $method->invokeArgs($controller, $args);

        return $this->sendResponse($ret);
    }


    /**
     * resolve a dependency from the container
     *
     * @throws \ReflectionException
     * @param string $name
     * @param array $params
     * @param mixed
     * @return mixed
     */
    public function resolve($name, $params = [])
    {
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
     * @param array $urlParams
     * @return mixed
     *
     * @throws \ReflectionException
     */
    private function resolveDependency(\ReflectionParameter $param, $urlParams = [])
    {
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


    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function notFound()
    {
        $handler = $this->getContainer()['notFoundHandler'];

        return $handler($this->getContainer()['request'], $this->getContainer()['response']);
    }


    /**
     * @param int $httpCode
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public function code($httpCode = 200)
    {
        return $this->resolve('response')->withStatus($httpCode);
    }


    /**
     * @param mixed $msg
     * @param int $code
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \ReflectionException
     */
    function error($msg, $code = 500)
    {
        if ($this->isConsole()) {
            return $this->resolve('response')
                ->withStatus($code)
                ->withHeader('Content-type', 'text/plain')
                ->write($msg);
        }

        if ($this->resolve('request')->getHeaderLine('Accept') == 'application/json') {
            if ($code == 422 && !is_array($msg)) {
                $msg = [$msg];
            }

            return $this->resolve('response')
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($code)
                ->withJson($msg);
        }

        $resp = $this->resolve('view')->render('http::error', ['code' => $code, 'message' => $msg]);

        return $this->resolve('response')
            ->withStatus($code)
            ->write($resp);
    }

}