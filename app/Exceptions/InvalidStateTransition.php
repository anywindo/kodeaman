<?php

namespace App\Exceptions;

use Exception;

class InvalidStateTransition extends Exception
{
    public function __construct(string $message = "Invalid state transition for the order.")
    {
        parent::__construct($message);
    }
}
