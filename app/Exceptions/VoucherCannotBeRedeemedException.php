<?php

namespace App\Exceptions;

use Exception;

class VoucherCannotBeRedeemedException extends Exception
{
    protected $message = 'Voucher cannot be redeemed at this time.';

    public function __construct($message = null)
    {
        parent::__construct($message ?? $this->message);
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage()
        ], 422);
    }
}
