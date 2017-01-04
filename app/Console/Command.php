<?php

namespace App\Console;


Abstract class Command
{

	/** @var \Psr\Http\Message\ServerRequestInterface */
	public $request;
	/** @var \Psr\Http\Message\ResponseInterface */
	public $response;
	/** @var \Psr\Log\LoggerInterface */
	public $logger;

	public function __construct($request, $response, $logger = null)
	{
		$this->request = $request;
		$this->response = $response;
		$this->logger = $logger;
	}

}