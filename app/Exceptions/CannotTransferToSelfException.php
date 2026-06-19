<?php

namespace App\Exceptions;

use Exception;

class CannotTransferToSelfException extends Exception
{
    public function __construct($message = "Cannot transfer to self", $code = 422, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
