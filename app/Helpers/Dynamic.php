<?php
/**
 * Created by PhpStorm.
 * User: jerfeson
 * Date: 26/11/18
 * Time: 21:47
 */

namespace App\Helpers;

class Dynamic extends \stdClass
{
    
    public function __call($key, $params)
    {
        if (!isset($this->{$key})) throw new Exception("Call to undefined method " . get_class($this) . "::" . $key . "()");
        $subject = $this->{$key};
        call_user_func_array($subject, $params);
    }
}