<?php

namespace App\Console;
use League\Plates\Engine;
use Psr\Log\LoggerInterface;

Abstract class Command
{

	public $logger;
	public $view;

	public function __construct(Engine $view, LoggerInterface $logger)
	{
		$this->logger = $logger;
		$this->view = $view;
	}

}