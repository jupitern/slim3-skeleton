<?php

namespace App\Model\Menu;
use App\Model\Model;
use Respect\Validation\Validator as v;

/**
 * @property int MenuID
 * @property string Role
 * @property string Description
 */
class Menu extends Model {

    protected $table = 'Menus';
    protected $primaryKey = 'MenuID';
    protected $fillable = ['Menu', 'Description'];
    protected $guarded = ['MenuID'];
    protected $casts = [
        'MenuID' => 'integer',
        'Menu' => 'string',
        'Description' => 'string'
    ];

    public $labels = [
        'MenuID' => 'Menu ID',
        'Menu' => 'Menu',
        'Description' => 'Description',
        'AppActions' => 'Actions',
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
                return $validator::key('Menu', v::notBlank()->length(0,100))
                    ->key('Description', v::length(0,255));
                break;
        }
        return $validator;
    }

    /* Relations */
    public function menuItems()
    {
        return $this->hasMany('App\Model\Menu\MenuItem', 'MenuID');
    }




}