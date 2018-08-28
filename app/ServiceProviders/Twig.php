<?php

namespace App\ServiceProviders;


class Twig implements ProviderInterface
{

	public static function register()
	{
        app()->getContainer()[\Slim\Views\Twig::class] = function ($c) {
            $settings = app()->getConfig('settings');
            $twig = new \Slim\Views\Twig($settings['templates'], $settings['twig']);
            $twig->addExtension(new \Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
            $twig->addExtension(new \Twig_Extension_Debug());

            return $twig;
        };
	}

}