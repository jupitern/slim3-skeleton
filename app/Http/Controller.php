<?php

namespace App\Http;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

Abstract class Controller
{
	public $request;
	public $response;
	public $logger;
	public $view;

	/**
	 * @param \League\Plates\Engine $view
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function __construct(\League\Plates\Engine $view, \Psr\Log\LoggerInterface $logger)
	{
		$this->request = app()->resolve('request');
		$this->response = app()->resolve('response');
		$this->view = $view;
		$this->logger = $logger;
	}

}