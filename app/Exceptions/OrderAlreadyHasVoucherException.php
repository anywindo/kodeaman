<?php

namespace App\Exceptions;

use Exception;

class OrderAlreadyHasVoucherException extends Exception
{
    protected $message = 'Order already has a voucher applied.';

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage()
        ], 422);
    }
}
