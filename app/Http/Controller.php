<?php

namespace App\Http;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \League\Plates\Engine;
use \Psr\Log\LoggerInterface;

Abstract class Controller
{
	public $request;
	public $response;
	public $logger;
	public $view;

	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @param \League\Plates\Engine $view
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function __construct(Request $request, Response $response, Engine $view, LoggerInterface $logger)
	{
		$this->request = $request;
		$this->response = $response;
		$this->view = $view;
		$this->logger = $logger;
	}

}