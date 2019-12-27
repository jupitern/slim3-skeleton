<?php namespace App\Model;

/**
 * Class Model
 * @package App\Model
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model {

    public $timestamps = true;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }
}