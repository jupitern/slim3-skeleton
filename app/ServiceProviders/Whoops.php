<?php

namespace App\ServiceProviders;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Whoops implements ProviderInterface
{

	public static function register()
	{
		$whoops = new Run;
        $whoops->allowQuit(false);
        $handler = new PrettyPageHandler;
        $whoops->pushHandler($handler);
        $whoops->register();
	}

}