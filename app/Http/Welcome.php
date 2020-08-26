<?php

namespace App\Http;
use Jupitern\Slim3\App\Http\Controller;

class Welcome extends Controller
{

    public function index($name = '', $logger)
    {
        return app()->view->render('http::welcome', ['name' => $name]);
    }


    public function method($name, \App\Model\User $user)
    {
        return get_class($user)."<br/>name = {$name}";
    }


    public function tests()
    {
        // save user info in session
        \Jupitern\Slim3\Utils\Session::set('user', ['id' => '1']);
        // get user info from session
        $uservar = \Jupitern\Slim3\Utils\Session::get('user');
        var_dump($uservar);

        $filesystem = app()->resolve('fs_local');
        $contents = $filesystem->listContents(STORAGE_PATH, true);
        var_dump($contents);

        /** @var \Jupitern\Slim3\Utils\Redis $cache */
        $cache = app()->resolve('redis');
        $cache->set("cacheKey", "some test value");
        echo $cache->get("cacheKey");
    }

}
