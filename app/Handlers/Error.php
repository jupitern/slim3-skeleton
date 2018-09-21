<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use NunoMaduro\Collision\Provider as Collision;

final class Error extends \Slim\Handlers\Error
{

    public function __invoke(Request $request, Response $response, \Exception $exception)
    {
        $app = app();
        $container = $app->getContainer();

        // Log the message
        if ($container->has(LoggerInterface::class)) {
            $app->resolve(LoggerInterface::class)->error($exception);
        }

        if ($app->console && class_exists(Collision::class)) {
            throw $exception;
        }

        if (!$this->displayErrorDetails) {
            return app()->error($exception->getMessage(), 500);
        }

        return parent::__invoke($request, $response, $exception);
    }

}