<?php

namespace App\Http\Site;
use \App\Http\Controller;

class Welcome extends Controller
{

	public function index($id='')
	{
		// log some message
		$this->logger->info("logging a message");

		// sending a response
		$this->response->write(
			$this->view->render('site::test/welcome', ['name' => $id])
		);
	}

}