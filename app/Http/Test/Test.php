<?php
namespace App\Http\Test;
use App\Http\Controller;
use GuzzleHttp\Client;

class Test extends Controller
{

	public function test($id, \App\Model\User $user)
	{
		echo get_class($this->view).'<br/>';
		echo "a = {$id}<br/>";
	}

}