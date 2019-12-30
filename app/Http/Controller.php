<?php

namespace App\Http;
use League\OAuth2\Server\AuthorizationServer;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \League\Plates\Engine as Plates;
use \Slim\Views\Twig;
use \Psr\Log\LoggerInterface;

/**
 * Class Controller
 * @package App\Http
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
Abstract class Controller
{
	public $request;
	public $response;
	public $logger;
	public $view;
    public $oAuthServer;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \League\Plates\Engine $view
     * @param \Psr\Log\LoggerInterface $logger
     * @param AuthorizationServer $oAuthServer
     */
	public function __construct(
	    Request $request,
        Response $response,
        Plates $view,
        LoggerInterface $logger,
        AuthorizationServer $oAuthServer
    )
	{
		$this->request = $request;
		$this->response = $response;
		$this->view = $view;
		$this->logger = $logger;
        $this->oAuthServer = $oAuthServer;
	}

}