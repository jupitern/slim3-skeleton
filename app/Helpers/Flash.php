<?php
/**
 * Created by PhpStorm.
 * User: jerfeson
 * Date: 26/11/18
 * Time: 21:45
 */

namespace App\Helpers;

use Slim\Flash\Messages;

class Flash
{
    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;
    
    /**
     * @param Messages $messages
     *
     * @return Dynamic|string
     */
    public static function getMessage(Messages $messages)
    {
        $message = "";
    
        if ($messages->hasMessage(self::STATUS_SUCCESS)) {
            $message = new Dynamic();
        
            $message->class = 'alert-success';
            $message->text = $messages->getFirstMessage(self::STATUS_SUCCESS);
        
        } elseif ($messages->hasMessage(self::STATUS_ERROR)) {
            $message = new Dynamic();
        
            $message->class = 'alert-danger';
            $message->text = $messages->getFirstMessage(self::STATUS_ERROR);
        
        }
    
        return $message;
    }
}