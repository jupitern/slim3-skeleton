<?php

namespace App\Handlers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use App\ServiceProviders\SlashTrace;

final class Error extends \Slim\Handlers\Error
{

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param \Exception                               $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response, \Exception $exception)
    {
        $app = app();
        $container = $app->getContainer();

        // Log the message
        if ($container->has(LoggerInterface::class) && !$this->displayErrorDetails) {
            $app->resolve(LoggerInterface::class)->error($exception);
        }

        if (app()->has('slashtrace') && ($app->isConsole() || $this->displayErrorDetails)) {
            app()->resolve('slashtrace')->register();
            http_response_code(500);
            throw $exception;
        }

        if (!$this->displayErrorDetails) {
            return app()->error($exception->getMessage(), 500);
        }

        return parent::__invoke($request, $response, $exception);
    }

}