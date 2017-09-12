<?php
/**
 * Created by PhpStorm.
 * User: pezo
 * Date: 2016.04.21.
 * Time: 12:30
 */

namespace App\Exceptions;


class ValidationException extends \Exception
{
    protected $validationErrors;

    public function __construct($message="", $code=0 , Exception $previous=null, $validationErrors = [])
    {
        $this->validationErrors = $validationErrors;

        parent::__construct($message, $code, $previous);
    }

    public function getMessages()
    {
        return $this->validationErrors;
    }
}