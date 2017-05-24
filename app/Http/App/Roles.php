<?php

namespace App\Http\App;
use App\Http\Controller;
use App\Model\Role;
use Respect\Validation\Exceptions\NestedValidationException;

class Roles extends Controller
{

    public function admin()
    {
        $model = new Role();
        return $this->view->render('app::roles/admin', [
            'model' => $model,
            'gridName' => 'rolesGrid'
        ]);
    }


    public function getData()
    {

        if (!$this->request->isXhr()) {
            throw new \Exception("Page not available");
        }

        $post = $this->request->getParsedBody();
        $searchFields = isset($post['searchFields']) ? $post['searchFields'] : [];

        $data = Role::query()
            ->selectRaw(
                'RoleID, Role, Description'
            )
            ->compareNumeric('RoleID', $searchFields['RoleID'])
            ->compareString('Role', $searchFields['Role'])
            ->compareString('Description', $searchFields['Description'])
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
        $model = Role::find($id);
        if (!$model) $model = new Role();

        if ($this->request->isPost()) {
            $post = $this->request->getParsedBody();
            $model->fill($post);

            try {
                $model->getValidator()->assert($model->getAttributes());
                $success = $model->save();
                $model->appactions()->sync(isset($post['appactions']) ? $post['appactions'] : []);
                //$model->roles()->sync(isset($post['roles']) ? $post['roles'] : []);
            }
            catch (NestedValidationException $e) {
                $errors = $e->getMessages();
            }
        }

        return $this->view->render('app::roles/edit', [
            'model' => $model,
            'success' => $success,
            'errors' => $errors,
        ]);
    }

    public function delete($id)
    {
        $model = Role::findOrFail($id);
        $model->delete();
    }

}