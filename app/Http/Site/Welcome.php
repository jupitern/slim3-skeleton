<?php

namespace App\Http\Site;
use \App\Http\Controller;

class Welcome extends Controller
{

	public function index()
	{
		// log some message
		$this->logger->info("log a message");

		if ($this->view instanceof \Slim\Views\Twig) {
            $this->view->render($this->response, "@site/test/welcome.twig");
        }
        elseif ($this->view instanceof \League\Plates\Engine) {
            return $this->view->render('site::test/welcome');
		}
	}

}