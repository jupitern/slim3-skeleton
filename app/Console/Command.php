<?php

namespace App\Console;


Abstract class Command
{
	public $request;
	public $response;
	public $logger;

	public function __construct($request, $response, $logger = null)
	{
		$this->request = $request;
		$this->response = $response;
		$this->logger = $logger;
	}

}