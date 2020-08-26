# Slim Framework 3 Skeleton Application (http + cli)

Use this skeleton application to quickly setup and start working on a new Slim Framework 3 application (Tested with slim 3.12).
This application handles http and command line requests.
This application ships with a few service providers and a session middleware out of the box.
Supports container resolution and auto-wiring.

To remove a service provider comment it on config/app.php file and remove it from composer.json, update composer.

Available service providers:

* [SlashTrace](https://github.com/slashtrace/slashtrace)
* [Monolog](https://github.com/Seldaek/monolog)
* [Eloquent](https://github.com/illuminate/database)
* [Plates](https://github.com/thephpleague/plates)
* [Twig](https://github.com/twigphp/Twig)
* [Flysystem](https://github.com/thephpleague/flysystem)
* [PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [Redis Cache](https://github.com/predis/predis)

Available middleware:

* Session

### Install the Application

Run this command from the directory in which you want to install your new Slim Framework application.

    php composer.phar create-project jupitern/slim3-skeleton [my-app-name]

Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `storage/` is web writable.
* make the necessary changes in config file config/app.php

### Run it:

1. `$ cd [my-app-name]\public`
2. `$ php -S localhost:8080`
3. Browse to http://localhost:8080


### Key directories

* `app`:        Application code (models, controllers, cli commands, handlers, middleware, service providers and others)
* `config`:     Configuration files like db, mail, routes...
* `lib`:        Other project classes like utils, business logic and framework extensions
* `resources`:  Views as well as your raw, un-compiled assets such as LESS, SASS, or JavaScript.
* `storage`:    Log files, cache files...
* `public`:     The public directory contains `index.php` file, assets such as images, JavaScript, and CSS
* `vendor`:     Composer dependencies

### Routing and dependency injection

The app class has a route resolver method that:
* matches and injects params into the controller action passed as uri arguments
* looks up and injects dependencies from the container by matching controller constructor / method argument class names
* automatic Resolution using controller constructor / method argument types
* accepts string or Response object as controller action response

Example defining two routes for a website and backend folders:

```php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// simple route example
$app->get('/welcome/{name}', function (Request $request, Response $response, $args) {
	$name = $request->getAttribute('name');
	$response->getBody()->write("Hello, $name");

	return $response;
});


// example route to resolve request to uri '/' to \App\Http\Site\Welcome::index
$app->any('/', function ($request, $response, $args) use($app) {
	return $app->resolveRoute([\App\Http\Welcome::class, "index"], $args);
});


// example calling http://localhost:8080/index.php/test/nuno with the route bellow
// injects the :name param value into the method $name parameter
// Other parameters in the method will be searched in the container by classname or automatically resolved
// in this example the resolveRoute method will create a user instance and inject it in the controller method
$app->any('/test[/{name}]', function ($request, $response, $args) use($app) {
	return $app->resolveRoute([\App\Http\Welcome::class, "method"], $args);
});

namespace App\Http;
use Jupitern\Slim3\App\Http\Controller;

class Welcome extends Controller
{
	public function method($name, \App\Model\User $user)
	{
	    return get_class($user)."<br/>name = {$name}";
	}
}

```

### Console usage

* Usage: php cli.php [command-name] [method-name] [parameters...]
* Help: php cli.php help

How to create a new command:
 1. Create a class under directory app\Console in namespace App\Console
 2. Your class should extend \App\Console\Command
 3. create a public method with some params.
 4. DONE!

Example:

Command class:
```php
namespace App\Console;

class Test extends Command
{

	public function method($a, $b='foobar')
	{
		return
			"\nEntered console command with params: \n".
			"a= {$a}\n".
			"b= {$b}\n";
	}
}
```

Execute the class:method from command line:

```php
// since param "b" is optional you can use one of the following commands

> php cli.php Test method a=foo b=bar

> php cli.php Test method a=foo
```

### Code examples

Get application instance
```php
$app = \Lib\Framework\App::instance();
// or simpler using a helper function
$app = app();
```

Debug a variable, array or object using the debug helper function
```php
debug(['a', 'b', 'c']);
// or debug and exit passing true as second param
debug(['a', 'b', 'c'], true);
```


Read a user from db using Laravel Eloquent service provider
```php
$user = \App\Model\User::find(1);
echo $user->Name;
```

Send a email using PHPMailer service provider service named 'mail' on config file
```php
/* @var $mail \PHPMailer\PHPMailer\PHPMailer */
$mail = app()->resolve('mail');
$mail->addAddress('john.doe@domain.com');
$mail->Subject = "test";
$mail->Body    = "<b>test body</b>";
$mail->AltBody = "alt body";
$mail->send();
```

List a directory content with Flysystem service provider named 'fs_local' on config file
```php
$filesystem = app()->resolve('fs_local');
$contents = $filesystem->listContents(STORAGE_PATH, true);
var_dump($contents);
```

Write and read from session using Session Helper class
```php
// save user info in session
\Jupitern\Slim3\Utils\Session::set('user', ['id' => '1']);
// get user info from session
$uservar = \Jupitern\Slim3\Utils\Session::get('user');
var_dump($uservar);
```

Write and read from cache with Redis service provider named 'redis' on config file
```php
/** @var \Jupitern\Slim3\Utils\Redis $cache */
$cache = app()->resolve('redis');
$cache->set("cacheKey", "some test value");
echo $cache->get("cacheKey");
```

## Changelog

v3.0
 - moved core code to another package.
 - route resolution using reflection can now be switched off for performance.
 - config file services changed structure.
 - register services in container using a string instead of classnames.
 - code refactor and improvements.

v2.6
 - Replaced Whoops and Collision packages by slashtrace that provides http and cli debug

V2.5
 - Allow for providers and middleware to be registered only for a given scope (dependent on app name)
 - general code improvements and error handling when developing rest api


## Roadmap

 - [ ] more service providers / separate service providers in packages
 - [ ] more code examples

## Contributing

 - welcome to discuss a bugs, features and ideas.

## License

jupitern/slim3-skeleton is release under the MIT license.
