<?php 

namespace App\Model;
use Respect\Validation\Validator as v;

/**
 * @property int RoleID
 * @property string Role
 * @property string Description
 */
class Role extends Model {

	protected $table = 'Roles';
	protected $primaryKey = 'RoleID';
	protected $fillable = ['Role', 'Description'];
	protected $guarded = [];
	protected $casts = [
		'RoleID' => 'integer',
		'Role' => 'string',
		'Description' => 'string'
	];

	public $labels = [
		'RoleID' => 'Role ID',
		'Role' => 'Role',
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
				return $validator::key('Role', v::notBlank()->length(0,100))
					->key('Description', v::length(0,255));
			break;
		}
		return $validator;
	}
	
	/* Relations */

	public function appActions()
	{ 
		return $this->belongsToMany('App\Model\AppAction', 'AppActionRoles', 'RoleID', 'AppActionID');
	}

	public function users()
	{
		return $this->belongsToMany('App\Model\User', 'UserRoles', 'RoleID', 'UserID');
	}



}