<?php

namespace App\Http;


Abstract class Controller
{
	public $request;
	public $response;
	public $logger;
	public $view;

	public function __construct($request, $response, $view, $logger = null)
	{
		$this->request = $request;
		$this->response = $response;
		$this->view = $view;
		$this->logger = $logger;
	}

}