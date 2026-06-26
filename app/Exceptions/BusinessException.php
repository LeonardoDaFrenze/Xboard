<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    /**
     * @param array $codeResponse [code, message] tuple
     * @param string $info Custom message — overrides the codeResponse message when non-empty
     */
    public function __construct(array $codeResponse, $info = '')
    {
        [$code, $message] = $codeResponse;
        parent::__construct($info ?: $message, $code);
    }
}