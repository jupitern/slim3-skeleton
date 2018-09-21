<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Whoops\Run;
use NunoMaduro\Collision\Provider as Collision;

final class PhpError extends \Slim\Handlers\PhpError
{

	public function __invoke(Request $request, Response $response, \Throwable $error)
	{
        $app = app();
        $container = $app->getContainer();

        // Log the message
        if ($container->has(LoggerInterface::class)) {
            $app->resolve(LoggerInterface::class)->error($error);
        }

        if ($app->console && class_exists(Collision::class)) {
            throw $error;
        }

        if (!$this->displayErrorDetails) {
            return app()->error($error->getMessage(), 500);
        }

        if (class_exists(Run::class)) {
            throw $error;
        }

        return parent::__invoke($request, $response, $error);
	}

}