<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\ServiceProviders\SlashTrace;

final class PhpError extends \Slim\Handlers\PhpError
{

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param \Throwable                               $error
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function __invoke(Request $request, Response $response, \Throwable $error)
    {
        $app = app();
        $container = $app->getContainer();

        // Log the message
        if ($container->has(LoggerInterface::class) && !$this->displayErrorDetails) {
            $app->resolve(LoggerInterface::class)->error($error);
        }

        if (app()->has('slashtrace') && ($app->isConsole() || $this->displayErrorDetails)) {
            app()->resolve('slashtrace')->register();
            http_response_code(500);
            throw $error;
        }

        if (!$this->displayErrorDetails) {
            return app()->error($error->getMessage(), 500);
        }

        return parent::__invoke($request, $response, $error);
    }

}