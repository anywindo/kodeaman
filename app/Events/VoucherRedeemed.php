<?php

namespace App\Events;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherRedeemed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $voucher;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Voucher $voucher, User $user)
    {
        $this->voucher = $voucher;
        $this->user = $user;
    }
}
