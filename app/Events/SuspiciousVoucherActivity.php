<?php

namespace App\Events;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuspiciousVoucherActivity
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $voucher;

    /**
     * Create a new event instance.
     */
    public function __construct(?User $user, ?Voucher $voucher)
    {
        $this->user = $user;
        $this->voucher = $voucher;
    }
}
