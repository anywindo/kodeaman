<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidStateTransition;
use App\Exceptions\ImmutableFieldException;
use App\Exceptions\CannotRefundException;
use App\Events\OrderPaid;
use App\Events\OrderRefunded;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'status' => OrderStatus::class,
        'order_date' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function setStatusAttribute($value)
    {
        if ($value !== null && !$value instanceof OrderStatus && !OrderStatus::tryFrom($value)) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->attributes['status'] = $value instanceof OrderStatus ? $value->value : $value;
    }
    
    public function setAmountAttribute($value)
    {
        if ($this->exists) {
            throw new ImmutableFieldException('amount');
        }
        $this->attributes['amount'] = $value;
    }

    public function confirmPayment(): void
    {
        if ($this->status !== OrderStatus::PENDING) {
            throw new InvalidStateTransition('Order must be in PENDING state to confirm payment.');
        }
        $this->status = OrderStatus::PAID;
        $this->paid_at = now();
        $this->save();
        event(new OrderPaid($this));
    }
    
    public function ship(): void
    {
        if ($this->status !== OrderStatus::PAID) {
            throw new InvalidStateTransition('Cannot ship order that is not paid');
        }
        $this->status = OrderStatus::SHIPPED;
        $this->shipped_at = now();
        $this->save();
    }
    
    public function confirmDelivery(): void
    {
        if ($this->status !== OrderStatus::SHIPPED) {
            throw new InvalidStateTransition('Order must be in SHIPPED state to be delivered.');
        }
        $this->status = OrderStatus::DELIVERED;
        $this->delivered_at = now();
        $this->save();
    }
    
    public function requestRefund(string $reason): void
    {
        if ($this->status !== OrderStatus::DELIVERED) {
            throw new CannotRefundException('Can only refund delivered orders');
        }
        $this->status = OrderStatus::REFUND_REQUESTED;
        $this->refund_requested_at = now();
        $this->refund_reason = $reason;
        $this->save();
    }
    
    public function approveRefund(): void
    {
        if ($this->status !== OrderStatus::REFUND_REQUESTED) {
            throw new InvalidStateTransition('No refund request found');
        }
        $this->status = OrderStatus::REFUNDED;
        $this->refunded_at = now();
        $this->save();
        event(new OrderRefunded($this));
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
