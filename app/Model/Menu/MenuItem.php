<?php

namespace App\Model\Menu;
use App\Model\Model;
use Respect\Validation\Validator as v;

/**
 * @property int MenuID
 * @property string Role
 * @property string Description
 */
class MenuItem extends Model {

    protected $table = 'MenuItems';
    protected $primaryKey = 'MenuItemID';
    protected $fillable = ['Label','MenuID', 'AppActionID','ExternalURL', 'OnClick','ParentID','Description', 'Active','Order'];
    protected $guarded = ['MenuItemID'];
    protected $casts = [
        'MenuItemID' => 'integer',
        'MenuID' => 'integer',
        'Label' => 'string',
        'AppActionID' => 'integer',
        'ExternalURL' => 'string',
        'OnClick' => 'string',
        'ParentID' => 'integer',
        'Description' => 'string',
        'Active' => 'boolean',
        'Order' => 'integer'
    ];

    public $labels = [
        'MenuItemID' => 'Menu Item ID',
        'MenuID' => 'Menu ID',
        'Label' => 'Label',
        'AppActionID' => 'Internal Action',
        'ExternalURL' => 'External URL',
        'OnClick' => 'On Click',
        'ParentID' => 'Parent',
        'Description' => 'Description',
        'Active' => 'Active',
        'Order' => 'Order'
    ];

    /**
     * Get Validator
     * @param string $scope
     * @return \Respect\Validation\Validator
     */
    public function getValidator($scope = 'default')
    {
        $validator = new v();
        switch ($scope) {
            case 'default':
                return $validator::key('Label', v::notBlank()->length(0,100))
                    ->key('Description', v::length(0,255));
                break;
        }
        return $validator;
    }

    /* Relations */
    public function menu()
    {
        return $this->belongsTo('App\Model\Menu\Menu');
    }

    public function parent()
    {
        return $this->hasOne('App\Model\Menu\MenuItem');
    }




}