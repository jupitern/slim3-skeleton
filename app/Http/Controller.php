<?php

namespace App\Http;


Abstract class Controller
{
	/** @var \Psr\Http\Message\ServerRequestInterface */
	public $request;
	/** @var \Psr\Http\Message\ResponseInterface */
	public $response;
	/** @var \Psr\Log\LoggerInterface */
	public $logger;
	/** @var \League\Plates\Engine */
	public $view;

	public function __construct($request, $response, $view, $logger = null)
	{
		$this->request = $request;
		$this->response = $response;
		$this->view = $view;
		$this->logger = $logger;
	}

}