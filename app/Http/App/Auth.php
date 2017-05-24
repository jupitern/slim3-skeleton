<?php
namespace App\Http\App;
use App\Http\Controller;
use Lib\Auth\AuthInterface;
use Lib\Auth\AuthLdap;
use Lib\Auth\AuthLocal;
use App\Model\User;
use Lib\Auth\Auth as Authentication;
use Lib\Utils\Session;
use Respect\Validation\Exceptions\NestedValidationException;

class Auth extends Controller
{

	public $username;
	public $password;
	public $rememberMe;
	private $errors;

	public function login()
	{
		// Login isn't accessible via ajax request
		if ($this->request->isXhr()) {
			throw new \Exception("Page not available");
		}

		$error = null;
		if ($this->request->isPost()) {

			$username = isset($_POST['Name']) ? $_POST['Name'] : '';
			$password = isset($_POST['Password']) ? $_POST['Password'] : '';

			$user = $this->findUser($username);

			$auth = new Authentication(
				$user && !empty($user->Password) ? new AuthLocal : new AuthLdap
			);

			if ($auth->login($username, $password)) {

				$user = $this->saveUser($user, AuthLdap::getUser($username));
				Session::set('user', $user);

				return $this->response->withRedirect($this->url('users/admin'));
			}

			$error = $auth->error;
		}
		// if User as session logged, redirect ro home
		if(Session::get('user')){
			return $this->response->withRedirect($this->url('users/admin'));
		}
		
		return $this->view->render('app::auth/login',[
			'error' => $error
		]);
	}

	public function logout()
	{
		\Lib\Auth\Auth::logout();
		return $this->response->withRedirect($this->url('auth/login'));
	}


	private function saveUser( $user, $userAD)
	{
		$success = false;
		$new = false;
		if (!$user) {
			$user = new \App\Model\User;
			$user->Username = $userAD['samaccountname'][0];
			$new = true;
		}
		$user->Name         = $userAD['displayname'][0];
		$user->Email        = $userAD['mail'][0];
		$user->PhoneExtension    = $userAD['telephonenumber'][0];
		$user->Phone		= $userAD['homephone'][0];
		$user->Department	= $userAD['department'][0];
		$user->JobTitle		= $userAD['title'][0];
		$user->Photo        = base64_encode($userAD['thumbnailphoto'][0]);
		//$user->DateLastLogin = new \DateTime(); //date('Y-m-d H:i:s');

		try {
			$user->getValidator()->assert($user->getAttributes());
			$success = $user->save();
			if ($success && $new) { //If new user then add default role (RoleID=1)
				$defaultRole = \App\Model\Role::find(1);
				$user->roles()->save($defaultRole);
			}
		}
		catch (NestedValidationException $e) {
			$this->logger->error("error updating user from AD: ".implode("\n", $e->getMessages()));
		}
		if($success) {
			return $user;
		}
		else
			return null;
	}



	private function findUser($username)
	{
		return \App\Model\User::query()->select('UserID','Username', 'Password')
			->where('username', '=', $username)->with('roles')
			->first();
	}

}