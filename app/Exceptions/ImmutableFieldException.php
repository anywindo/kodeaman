<?php

namespace App\Exceptions;

use Exception;

class ImmutableFieldException extends Exception
{
    public function __construct(string $field)
    {
        parent::__construct("The field '{$field}' is immutable and cannot be changed.");
    }
}
