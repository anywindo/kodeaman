<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class LogOrderEvent
{
    public function handle($event): void
    {
        $order = $event->order;
        
        AuditLog::create([
            'event' => class_basename($event),
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'data' => [
                'status' => $order->status->value ?? $order->status,
                'amount' => $order->amount,
            ],
            'created_at' => now(),
        ]);
    }
}
