<?php

namespace App\ServiceProviders;

use Slim\Flash\Messages;

class Flash implements ProviderInterface
{

	public static function register()
	{
		app()->getContainer()[Messages::class] = function ($c) {
		    $flash =  new \Slim\Flash\Messages;
			return $flash;
		};
    
    }

}
