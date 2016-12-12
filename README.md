# Slim Framework 3 Skeleton Application (http + cli)

Use this skeleton application to quickly setup and start working on a new Slim Framework 3 application (Developed with slim 3.6).
This application handles http and command line requests.
The application has service providers available. All are optional and you can disable them in the config/app.php file

Available service providers:

* Whoops
* Monolog
* Eloquent
* Plates
* Flysystem
* PHPMailer

Available Middleware:

* Session

Third party service providers should also work out of the box.

### Install the Application

Run this command from the directory in which you want to install your new Slim Framework application.

    php composer.phar create-project jupitern/slim3-skeleton [my-app-name] --stability=dev

Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `storage/` is web writable.
* make the necessary changes in config file config/app.php

### Run it:

1. `$ cd [my-app-name]`
2. `$ php -S 0.0.0.0:8888 -t public public/index.php`
3. Browse to http://localhost:8888


### Key directories

* `app`:        Application code (models, controllers, cli commands, handlers, middleware, service providers and others)
* `config`:     Configuration files like db, mail, routes...
* `lib`:        Agnostic project classes
* `resources`:  Views as well as your raw, un-compiled assets such as LESS, SASS, or JavaScript.
* `storage`:    Log files, cache files...
* `public`:     The public directory contains `index.php` file, assets such as images, JavaScript, and CSS
* `vendor`:     Composer dependencies

### Routing

This app comes with generic routes defines that will try to automatically match a uri with a class:method
and inject dependencies matching param names to container object indexes or a route argument.
The routes bellow are a example for generic routing to all class:method in
You can always define your routes one by one and use (or not) $app->resolveRoute method to inject your dependencies.
Automatic injection will work for class constructor also. BaseController.php
Example defining two routes for a website and app (backend) folders:

```php

// resolves to a class:method under the namespace \\App\\Http\\Site and
// Other parameters in the method will be searched in the container using parameter name
$app->any('/{class}/{method}', function ($request, $response, $args) use($app) {
	$app->resolveRoute($args, "\\App\\Http\\Site");
});

// resolves to a class:method under the namespace \\App\\Http\\App and
// injects the :id param value into the methos $id parameter
// Other parameters in the method will be searched in the container using parameter name
$app->any('/app/{class}/{method}[/{id:[0-9]+}]', function ($request, $response, $args) use($app) {
	$app->resolveRoute($args, "\\App\\Http\\App");
});

```

### Console usage

Just create your console classes under directory app\Console with namespace App\Console and execute in cli doing the following:

* usage: php cli.php <command-name> <method-name> [parameters...]
* help: php cli.php help

Console routes are located in file app/config/routes_cli.php.
This file contains a help route to list available routes a a route to resolve console commands to your classes
under the namespace App\Console

### Code examples

Log some message
```php
// As BaseController constructor is requesting $logger and exists in the container we can simple do:
$this->logger->info("logging a message");
```

Read a user from db
```php
$user = \App\Model\User::find(1);
echo $user->Name;
```

Send a email
```php
$app->resolve('mailer', [
    ['john_doe@domain.com'], [], [], 'test', 'test', 'teste alt body',
])->send();
```

List a directory content with flysystem
```php
$filesystem = $app->resolve('filesystem', ['local']);
var_dump($filesystem->listContents('', true));
```

Write and read from session
```php
\Lib\Session::set('user', ['id' => '1']);
print_r(\Lib\Session::get('user'));
```

## Roadmap

 - [ ] improve dependency injection resolution
 - [ ] more service providers
 - [ ] more examples

## Contributing

 - welcome to discuss a bugs, features and ideas.

## License

jupitern/slim3-skeleton is release under the MIT license.