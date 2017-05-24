<?php

namespace App\Http\App;
use App\Http\Controller;
use App\Model\User;
use Respect\Validation\Exceptions\NestedValidationException;

class Users extends Controller
{

	public function admin()
	{
		$model = new User();
		return $this->view->render('app::users/admin', [
			'model' => $model,
			'gridName' => 'usersGrid'
		]);
	}


	public function getData()
	{

		if (!$this->request->isXhr()) {
			throw new \Exception("Page not available");
		}

		$post = $this->request->getParsedBody();
		$searchFields = isset($post['searchFields']) ? $post['searchFields'] : [];

		$data = User::query()
			->selectRaw(
				'UserID, Username, Name, Email, Phone, PhoneExtension, JobTitle, Department, Active,
				DateLastLogin, CONVERT(DATE, DateCreated) as DateCreated'
			)
			->compareNumeric('UserID', $searchFields['UserID'])
			->compareString('Username', $searchFields['Username'])
			->compareString('Name', $searchFields['Name'])
			->compareDate('Email', $searchFields['Email'])
			->compareBoolean('Active', $searchFields['Active'])
			->offset($post['start'])
			->limit($post['length'])
			->setGridOrder($post)
			->get();

		return json_encode(['data' => $data]);
	}


	public function edit($id = null)
	{
		$success = false;
		$errors = [];
		$model = User::find($id);
		if (!$model) $model = new User();

		if ($this->request->isPost()) {
			$post = $this->request->getParsedBody();
			$model->fill($post);

			try {
				$model->getValidator()->assert($model->getAttributes());
				$success = $model->save();
				$model->roles()->sync(isset($post['roles']) ? $post['roles'] : []);
			}
			catch (NestedValidationException $e) {
				$errors = $e->getMessages();
			}
		}

		return $this->view->render('app::users/edit', [
			'model' => $model,
			'success' => $success,
			'errors' => $errors,
		]);
	}


	public function delete($id)
	{
		$model = User::findOrFail($id);
		$model->delete();
	}

}