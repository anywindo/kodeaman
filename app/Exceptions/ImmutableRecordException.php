<?php

namespace App\Exceptions;

use Exception;

class ImmutableRecordException extends Exception
{
    public function __construct($message = "Record is immutable", $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
