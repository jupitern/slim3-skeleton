<?php

namespace App\Http\App;
use App\Http\Controller;
use App\Model\Menu\Menu;
use Respect\Validation\Exceptions\NestedValidationException;

class Menus extends Controller
{

    public function admin()
    {
        $model = new Menu();
        return $this->view->render('app::menus/admin', [
            'model' => $model,
            'gridName' => 'menusGrid'
        ]);
    }


    public function getData()
    {

        if (!$this->request->isXhr()) {
            throw new \Exception("Page not available");
        }

        $post = $this->request->getParsedBody();
        $searchFields = isset($post['searchFields']) ? $post['searchFields'] : [];

        $data = Menu::query()
            ->selectRaw(
                'MenuID, Menu, Description'
            )
            ->compareNumeric('MenuID', $searchFields['MenuID'])
            ->compareString('Menu', $searchFields['Menu'])
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
        $model = Menu::find($id);

        if (!$model) $model = new Menu();

        //$menuItems = $model->menuItems->toArray();
        $menuItems = $model->menuItems->keyBy('MenuItemID')->toArray();
        
        $menuItemTypes = array(
            '' => '',
            '0' => 'Internal',
            '1' => 'External',
            '2' => 'On Click'
        );
       
        if ($this->request->isPost()) {
            $post = $this->request->getParsedBody();
            $model->fill($post);

            try {
                $model->getValidator()->assert($model->getAttributes());
                $success = $model->save();
                //$model->roles()->sync(isset($post['roles']) ? $post['roles'] : []);
            }
            catch (NestedValidationException $e) {
                $errors = $e->getMessages();
            }
        }

        return $this->view->render('app::menus/edit', [
            'model' => $model,
            'success' => $success,
            'errors' => $errors,
            'menuItems' => $menuItems,
            'menuItemTypes' => $menuItemTypes
        ]);
    }

    public function deleteEntry($id = null) {
        $model = \App\Model\Menu\Menu::findOrFail($id);
        $model->delete();
    }

    public function addMenuItem()
    {
        $errors = [];
        $menuItem = $_POST['newMenuItem'];
        if ($menuItem['MenuID'] == '') {
            $errors[] = "Problem Identifing the Menu, try to reload page.";
        }
        if ($menuItem['Label'] == '') {
            $errors[] = "Label required.";
        }
        if ($menuItem['menuItemType'] == '') {
            $errors[] = "Type of Action required.";
        }
        if ($menuItem['Action'] == '') {
            $errors[] = "Action required.";
        }
        if (count($errors)) {
            $response = array(
                'data' => [],
                'errors' => $errors
            );
        } else {
            if ($menuItem['MenuItemID'] != null) {
                $newMenuItem = \App\Model\Menu\MenuItem::find($menuItem['MenuItemID']);
            } else {

                $newMenuItem = new \App\Model\Menu\MenuItem;
            }

            $newMenuItem->Label = $menuItem['Label'];
            $newMenuItem->Active = $menuItem['Active'];

            switch ($menuItem['menuItemType']) {
                case '0':
                    $newMenuItem->AppActionID = $menuItem['Action'];
                    break;
                case '1':
                    $newMenuItem->ExternalURL = $menuItem['Action'];
                    break;
                case '2':
                    $newMenuItem->OnClick = $menuItem['Action'];
                    break;
            }
            if ($menuItem['ParentID']=="")
                $menuItem['ParentID']=null;
            $newMenuItem->ParentID = $menuItem['ParentID'];
            $newMenuItem->Active = $menuItem['Active'];
            $newMenuItem->Order = $menuItem['Order'];
            $newMenuItem->MenuID = $menuItem['MenuID'];
            try {
                $success = $newMenuItem->save();
                $model = Menu::find($menuItem['MenuID']);
                $menuItems = $model->menuItems->keyBy('MenuItemID')->toArray();
                $data = $menuItems;
            }
            catch (Exception $e) {
                $errors = $e->getMessages();
            }
            $response = array(
                'data' => $data,
                'errors' => $errors
            );
        }

        echo \GuzzleHttp\json_encode($response);
    }

    function deleteMenuItem($id) {
        $errors = [];
        $menuItemID = $_POST['menuItemID'];
        if ($id == '') {
            $errors[] = "Problem Identifing the Menu, try to reload page.";
        }
        if ($menuItemID == '') {
            $errors[] = "Invalid Menu Item.";
        }
        if (count($errors)) {
            $response = array(
                'data' => [],
                'errors' => $errors
            );
        } else {
            $model = \App\Model\Menu\MenuItem::findOrFail($menuItemID);
            try {
                $model->delete();
                $menuModel = Menu::find($id);
                $menuItems = $menuModel->menuItems->keyBy('MenuItemID')->toArray();
                $data = $menuItems;
            } catch (NestedValidationException $e) {
                $errors = $e->getMessages();
            }
            $response = array(
                'data' => $data,
                'errors' => $errors
            );
        }
        echo \GuzzleHttp\json_encode($response);

        /*

        $model = Menu::find($id);
        $menuItems = $model->menuItems->keyBy('MenuItemID')->toArray();
        echo \GuzzleHttp\json_encode($id);*/
    }

    private function prepareMenuItems(array &$menuItems, $parentId = 0) {
        $branch = array();
        foreach ($menuItems as $element) {
            if ($element['ParentID'] == $parentId) {
                $son = $this->prepareMenuItems($menuItems, $element['MenuItemID']);
                if ($son) {
                    $element['sons'] = $son;
                }
                $branch[$element['MenuItemID']] = $element;
            }
        }
        return $branch;
    }


    /*
    public function delete($id)
    {
        $model = User::findOrFail($id);
        $model->delete();
    }*/

}