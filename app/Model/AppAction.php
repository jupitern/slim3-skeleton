<?php 

namespace App\Model;
use Respect\Validation\Validator as v;

/**
 * @property int AppActionID
 * @property string Uri
 * @property boolean AuthRequired
 */
class AppAction extends Model {

	protected $table = 'AppActions';
	protected $primaryKey = 'AppActionID';

	protected $fillable = [];
	protected $casts = [
		'AppActionID' => 'integer',
		'Uri' => 'string',
		'AuthRequired' => 'boolean'
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
				return $validator::key('Uri', v::notBlank()->length(0,100));
			break;
		}
		return $validator;
	}
	
	/* Relations */

	public function roles()
	{ 
		return $this->belongsToMany('App\Model\Role', 'AppActionRoles', 'AppActionID', 'RoleID');
	}



}