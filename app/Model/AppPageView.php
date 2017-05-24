<?php 

namespace App\Model;
use Respect\Validation\Validator as v;

/**
 * @property int AppPageViewID
 * @property string SessionKey
 * @property string Uri
 * @property string DateCreated
 */
class AppPageView extends Model {

	protected $table = 'AppPageViews';
	protected $primaryKey = 'AppPageViewID';

	protected $guarded = [];


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
				return $validator::key('SessionKey', v::notBlank()->length(0,255))
					->key('Uri', v::notBlank()->length(0,255));
			break;
		}
		return $validator;
	}


	/* Relations */
	
}