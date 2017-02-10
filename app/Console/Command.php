<?php

namespace App\Console;

Abstract class Command
{

	/** @var \Psr\Log\LoggerInterface */
	public $logger;
	/** @var \League\Plates\Engine */
	public $view;

	public function __construct($view, $logger = null)
	{
		$this->logger = $logger;
		$this->view = $view;
	}

}