<?php


namespace App\Messages;

/**+
 * Class Message
 * @package App\Messages
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
class Message
{
    const STATUS_SUCCESS = "success";
    const STATUS_ERROR = "error";

    /*Authentication messages*/
    const ACCESS_DENIED = "Access denied";
    const LOGIN_SUCCESSFUL = "Login Successful";

    /*Default messages*/
    const UNKNOWN_ERROR = "An unknown error has occurred, contact your system administrator;.";

}