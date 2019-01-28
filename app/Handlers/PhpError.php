<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\ServiceProviders\SlashTrace;

final class PhpError extends \Slim\Handlers\PhpError
{

    public function __invoke(Request $request, Response $response, \Throwable $error)
    {
        $app = app();
        $container = $app->getContainer();

        // Log the message
        if ($container->has(LoggerInterface::class) && !$this->displayErrorDetails) {
            $app->resolve(LoggerInterface::class)->error($error);
        }

        if (class_exists(SlashTrace::class) && ($app->isConsole() || $this->displayErrorDetails)) {
            throw $error;
        }

        if (!$this->displayErrorDetails) {
            return app()->error($error->getMessage(), 500);
        }

        return parent::__invoke($request, $response, $error);
    }

}