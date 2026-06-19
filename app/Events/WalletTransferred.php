<?php

namespace App\Events;

use App\Models\Wallet;
use App\ValueObjects\Money;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletTransferred
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Wallet $from,
        public Wallet $to,
        public Money $amount
    ) {}
}
