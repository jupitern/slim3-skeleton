<?php 

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database;

class Eloquent
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		$dbSettings = app()->getConfig('settings.database');

		// register connections
		$capsule = new Capsule;
		foreach ($dbSettings as $name => $configs) {
			$capsule->addConnection($dbSettings[$name], $name);
		}
		
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

		app()->getContainer()[Capsule::class] = function ($c) {
			return function($driver = 'default') {
				$conn = Capsule::connection($driver);
				if ($conn->getConfig('profiling') == true) {
					$conn->enableQueryLog();
				}
				
				return $conn;
			};
		};

		return $next($request, $response);
	}
}