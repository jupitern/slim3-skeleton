<?php 

namespace App\Model;
use Respect\Validation\Validator as v;

/**
 * @property int CountryID
 * @property string Country
 * @property string CountryCode2
 * @property string CountryCode3
 * @property string PhoneCode
 * @property int Population
 * @property float PopulationShare
 * @property int Area
 * @property string CurrencyCode
 * @property string CurrencyName
 * @property string PostalCodeRegex
 */
class Country extends Model {

	protected $table = 'Countries';
	protected $primaryKey = 'CountryID';

	protected $guarded = [];

	protected $casts = [
		'CountryID' => 'integer',
		'Country' => 'string',
		'CountryCode2' => 'string',
		'CountryCode3' => 'string',
		'PhoneCode' => 'string',
		'Population' => 'integer',
		'PopulationShare' => 'float',
		'Area' => 'integer',
		'CurrencyCode' => 'string',
		'CurrencyName' => 'string',
		'PostalCodeRegex' => 'string'
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
				return $validator::key('Country', v::length(0,200))
					->key('CountryCode2', v::length(0,2))
					->key('CountryCode3', v::length(0,3))
					->key('PhoneCode', v::length(0,9))
					->key('Population', v::intVal())
					->key('PopulationShare', v::floatVal())
					->key('Area', v::intVal())
					->key('CurrencyCode', v::length(0,10))
					->key('CurrencyName', v::length(0,100))
					->key('PostalCodeRegex', v::length(0,200));
			break;
		}
		return $validator;
	}

	/* Relations */

	public function appVisitors()
	{ 
		return $this->hasMany('App\Model\AppVisitor', 'CountryID', 'CountryID');
	}

	public function postalCodes()
	{ 
		return $this->hasMany('App\Model\PostalCode', 'CountryID', 'CountryID');
	}

}