<?php

namespace App\ServiceProviders;
use SlashTrace\SlashTrace as ST;
use SlashTrace\EventHandler\DebugHandler;
use SlashTrace\DebugRenderer\DebugCliRenderer;

class SlashTrace implements ProviderInterface
{

	public static function register()
	{
	    $d = new DebugHandler();
	    if (php_sapi_name() === "cli") $d->setRenderer(new DebugCliRenderer());

        $st = new ST();
        $st->addHandler($d);
        $st->register();

        app()->getContainer()['slashtrace'] = $st;
	}

}