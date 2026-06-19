<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\ValueObjects\VoucherCode;
use App\Exceptions\ImmutableFieldException;
use InvalidArgumentException;

class Voucher extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_expired' => 'boolean',
        'is_used_up' => 'boolean',
        'is_first_order_only' => 'boolean',
        'is_stackable' => 'boolean',
        'is_reusable' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($voucher) {
            // Validasi format voucher code
            $code = VoucherCode::fromString($voucher->code);
            $voucher->code = $code->toString();

            // Validasi tipe discount
            if (!in_array($voucher->discount_type, ['percentage', 'fixed'])) {
                throw new InvalidArgumentException('Invalid discount type');
            }

            // Validasi discount value
            if ($voucher->discount_value < 0) {
                throw new InvalidArgumentException('Discount value cannot be negative');
            }
            if ($voucher->discount_type === 'percentage' && $voucher->discount_value > 100) {
                throw new InvalidArgumentException('Percentage cannot exceed 100');
            }

            // Validasi tanggal
            if ($voucher->valid_until && $voucher->valid_from && $voucher->valid_until <= $voucher->valid_from) {
                throw new InvalidArgumentException('valid_until must be after valid_from');
            }
            
            // Validasi usage
            if ($voucher->max_usage <= 0) {
                throw new InvalidArgumentException('max_usage must be positive');
            }
        });

        static::updating(function ($voucher) {
            // Immutable fields check
            if ($voucher->isDirty(['code', 'discount_type', 'valid_from'])) {
                throw new ImmutableFieldException('Cannot modify code, discount_type, or valid_from');
            }
            
            // Usage count tidak bisa dikurangi
            if ($voucher->isDirty('usage_count') && $voucher->usage_count < $voucher->getOriginal('usage_count')) {
                throw new InvalidArgumentException('usage_count cannot be decreased');
            }
        });
    }

    public function redemptions()
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isWithinValidityPeriod(): bool
    {
        $now = now();
        return $now->greaterThanOrEqualTo($this->valid_from) && $now->lessThanOrEqualTo($this->valid_until);
    }

    public function hasRemainingUsage(): bool
    {
        return $this->usage_count < $this->max_usage;
    }

    public function meetsMinimumPurchase(float $amount): bool
    {
        return $amount >= $this->min_purchase;
    }

    public function canBeRedeemed(User $user, Order $order): bool
    {
        if (!$this->isActive() || !$this->isWithinValidityPeriod()) {
            return false;
        }

        if (!$this->hasRemainingUsage()) {
            return false;
        }

        if (!$this->meetsMinimumPurchase($order->amount)) {
            return false;
        }

        if ($this->is_first_order_only) {
            // Cek apakah user pernah punya order selain ini yang berstatus sukses atau minimal punya order lama
            $pastOrders = Order::where('user_id', $user->id)
                                ->where('id', '!=', $order->id)
                                ->count();
            if ($pastOrders > 0) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($orderAmount * $this->discount_value) / 100;
        } else {
            $discount = $this->discount_value;
        }

        if ($this->max_discount !== null && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return $discount;
    }
}
