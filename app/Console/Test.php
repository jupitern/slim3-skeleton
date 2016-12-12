<?php

namespace App\Console;

class Test extends Command
{

	public function test($a, $b='foobar')
	{
		$this->logger->info("logging a message");

		$this->response->write(
			"\nEntered console command with params: \n".
			"a= {$a}\n".
			"b= {$b}\n"
		);
	}
}