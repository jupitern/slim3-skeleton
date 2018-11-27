<?php

namespace App\Console;
use League\Plates\Engine;
use Psr\Log\LoggerInterface;
use Jobby\Jobby;

Abstract class Command
{

	public $logger;
	public $view;
	public $jobby;

	public function __construct(Engine $view, LoggerInterface $logger, Jobby $jobby)
	{
		$this->logger = $logger;
		$this->view = $view;
		$this->jobby = $jobby;
	}

}