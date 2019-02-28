<?php

namespace App\ServiceProviders;
use SlashTrace\SlashTrace as ST;
use SlashTrace\EventHandler\DebugHandler;

class SlashTrace implements ProviderInterface
{

    public static function register()
    {
        $st = new ST();
        $st->addHandler(new DebugHandler());

        app()->getContainer()['slashtrace'] = $st;
    }

}