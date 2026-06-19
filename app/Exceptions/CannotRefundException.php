<?php

namespace App\Exceptions;

use Exception;

class CannotRefundException extends Exception
{
    public function __construct(string $message = "Order cannot be refunded at this stage.")
    {
        parent::__construct($message);
    }
}
