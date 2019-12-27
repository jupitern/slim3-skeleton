# Slim Framework 3 Skeleton Application (http + cli + oauth2)

Use this skeleton application to quickly setup and start working on a new Slim Framework 3 application (Tested with slim 3.12).
This application handles http and command line requests.
This application ships with a few service providers and a Session middleware out of the box.
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
* [Redis Cache](https://github.com/naroga/redis-cache)
* [Jobby](https://github.com/jobbyphp/jobby)
* [Oauth2](https://github.com/thephpleague/oauth2-server)

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
* looks up and injects dependencies from the container by matching controller constructor / method argument names
* automatic Resolution using controller constructor / method argument types
* accepts string or Response object as controller action response

Example defining two routes for a website and backend folders:

```php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// simple route example
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
	$name = $request->getAttribute('name');
	$response->getBody()->write("Hello, $name");

	return $response;
});


// example route to resolve request to uri '/' to \App\Http\Site\Welcome::index
$app->any('/', function ($request, $response, $args) use($app) {
	return $app->resolveRoute('\App\Http\Site', "Welcome", "index", $args);
});


// example using container resolution and automatic resolution (auto-wiring)
// resolves to a class:method under the namespace \App\Http\Site
// injects the :id param value into the method $id parameter

// route definition
$app->any('/{class}/{method}[/{id:[0-9]+}]', function ($request, $response, $args) use($app) {
	return $app->resolveRoute('\App\Http\Site', $args['class'], $args['method'], $args);
});

// Controller Welcome definition
namespace App\Http\Site;
use \App\Http\Controller;

class Welcome extends Controller
{
	public function index($id, \Psr\Log\LoggerInterface $logger, \App\Model\User $user)
	{
	    $logger->info("logging a message using logger resolved from container");

	    $logger->info("getting a eloquent user model attributes using automatic resolution (auto-wiring)");
	    debug($user->getAttributes()); // helper method debug

        // return response as string. resolveRoute method will handle it and output the response
	    return "id = {$id}";
	}
}


// example calling http://localhost:8080/index.php/app/test/method/1 with the route bellow
// resolves to a class:method under the namespace \App\Http\App and
// injects the :id param value into the method $id parameter
// Other parameters in the method will be searched in the container or automatically resolved

// route definition
$app->any('/app/{class}/{method}[/{id:[0-9]+}]', function ($request, $response, $args) use($app) {
	return $app->resolveRoute('\App\Http\App', $args['class'], $args['method'], $args);
});

namespace App\Http\App;
use \App\Http\Controller;

// Controller Test definition
class Test extends Controller
{
	public function method($id, \App\Model\User $user)
	{
	    return get_class($user)."<br/>id = {$id}";
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

Debug a variable, array or object using a debug helper
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

Send a email using PHPMailer service provider and default settings
```php
/* @var $mail \PHPMailer\PHPMailer\PHPMailer */
$mail = app()->resolve(\PHPMailer\PHPMailer\PHPMailer::class);
$mail->addAddress('john.doe@domain.com');
$mail->Subject = "test";
$mail->Body    = "<b>test body</b>";
$mail->AltBody = "alt body";
$mail->send();
```

List a directory content with Flysystem service provider and default settings 'local'
```php
$filesystem = app()->resolve(\League\Flysystem\FilesystemInterface::class, ['local']);
$contents = $filesystem->listContents('', true);
var_dump($contents);
```

Write and read from session using Session Helper class
```php
// save user info in session
\Lib\Utils\Session::set('user', ['id' => '1']);
// get user info from session
$user = \Lib\Utils\Session::get('user');
var_dump($user);
```

Write and read from cache (using default driver redis)
```php
/** @var \Naroga\RedisCache\Redis $cache */
$cache = app()->resolve(\Psr\SimpleCache\CacheInterface::class);
$cache->set("cacheKey", "some test value");
echo $cache->get("cacheKey");
```

### Jobby usage

Then add the following line to your (or whomever's) crontab:
```
* * * * * cd /path/to/project && php cli.php Jobby init 1>> /dev/null 2>&1
```

After Jobby installs, you can copy an example file to the project root.
```
$ cp vendor/hellogerard/jobby/resources/jobby.php .
```

### Oauth2 usage

First create a database, and include the configuration data in the `config/development.php or config/production.php` file
We now need to create the tables that will be used to manage oAuth2, To do this run the following command in the root folder of the project.

```
php cli.php Migrations migrate
```

This command will create the tables and data needed for a first test with Oauth2.
Do not forget to take a look at the executed file `app/Console/Migrations.php`;

For everything to work correctly, we need to create some keys, these ensure the integrity of the service.
You can create using the following [link](https://www.ssh.com/ssh/keygen)  
Copy files to `data/keys/oauth` folder
The settings for these files are in the `config/app.php`

To test your application use [postman](https://www.getpostman.com/).
Create a new request for the following route. `http://localhost/api/v1/authentication/authentication/login`
Navigate to the Authorization tab, select type oAuth2, click Get New Access Token and fill in the fields as follows

```
Grant type: Password Credentials 
Access token URL: http://localhost/api/v1/authentication/authentication/token
User name: admin
Password: admin
Client ID: QWRtaW5pc3RyYXRpb24=
Cliente Secret: Administration
```

Click on request token, then use token, after that submit the request
The answer will be something close to that.

```json
{
    "result": "success",
    "message": "Login Successful",
    "redirect_url": ""
}
```

Remember to create your token validation business rule
```
Middleware/OAuthAuthenticationToken.php
```
## Changelog

 
v2.6
 - Replaced Whoops and Collision packages by slashtrace that provides http and cli debug
 - Oauth2, Repository, Business

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
