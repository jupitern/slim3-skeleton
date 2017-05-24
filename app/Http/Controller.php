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
		$this->logger = $logger;
		$this->view = $view;
	}


	protected function url($url = '', $showIndex = null, $includeBaseUrl = true)
	{
		return \Lib\Framework\App::instance()->url($url, $showIndex, $includeBaseUrl);
	}

}