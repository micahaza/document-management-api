<?php
/**
 * Created by PhpStorm.
 * User: pezo
 * Date: 2016.04.21.
 * Time: 12:30
 */

namespace App\Exceptions;


class InvalidStatusChangeException extends \Exception
{
    public function __construct($message="", $code=0 , Exception $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }
}