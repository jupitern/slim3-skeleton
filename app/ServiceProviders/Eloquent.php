<?php namespace App\ServiceProviders;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Illuminate\Database;

class Eloquent
{
	private $container;

	public function __construct($container) {
		$this->container = $container;
	}

	public function __invoke($request, $response, $next)
	{
		$settings = \App\App::instance()->getConfig('settings');
		$debug = $settings['debug'];

		// register connections
		$capsule = new Capsule;
		foreach ($settings['database'] as $name => $configs) {
			$capsule->addConnection($settings['database'][$name], $name);
		}
		
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

		$this->container['database'] = function ($c) use ($debug) {
			return function($name = 'default') use ($debug) {
				$conn = Capsule::connection($name);
				if ($debug) {
					$conn->enableQueryLog();
				}
				return $conn;
			};
		};

		return $next($request, $response);
	}
}