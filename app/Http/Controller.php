<?php

namespace App\Http;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \League\Plates\Engine as Plates;
use Slim\Flash\Messages;
use \Slim\Views\Twig;
use \Psr\Log\LoggerInterface;

Abstract class Controller
{
	public $request;
	public $response;
	public $logger;
	public $view;
	public $message;
    
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param \League\Plates\Engine                    $view
     * @param \Psr\Log\LoggerInterface                 $logger
     * @param Messages                                 $message
     */
	public function __construct(Request $request, Response $response, Twig $view, LoggerInterface $logger, Messages $message)
	{
		$this->request = $request;
		$this->response = $response;
		$this->view = $view;
		$this->logger = $logger;
		$this->message = $message;
	}

}