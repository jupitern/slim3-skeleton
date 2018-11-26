<?php
/**
 * Created by PhpStorm.
 * User: Jerfeson Guerreiro
 * Date: 26/11/18
 * Time: 20:56
 */

namespace App\ServiceProviders;

class Jobby implements ProviderInterface
{
    
    public static function register()
    {
        app()->getContainer()[\Jobby\Jobby::class] = function ($c) {
            $jobby = new \Jobby\Jobby();
            return $jobby;
        };
        
    }
    
}
