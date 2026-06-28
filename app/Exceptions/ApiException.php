<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected $code; // Translation
    protected $message; // Error Code
    protected $errors; // All Error Information

    public function __construct($message = null, $code = 400, $errors = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->errors = $errors;
    }
    public function errors(){
        return $this->errors;
    }

}
