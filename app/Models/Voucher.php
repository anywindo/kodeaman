<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODUL 4: Voucher Model
 * 
 * MASALAH:
 * 1. Primitive obsession - semua field primitif
 * 2. Boolean flag hell
 * 3. Anemic model - tidak ada business logic
 * 4. Invalid state bisa direpresentasikan
 * 5. Tidak ada domain rules
 */
class Voucher extends Model
{
    // MASALAH: Semua field fillable, tidak ada protection
    protected $fillable = [
        'code',
        'discount_type', // String: "percentage", "fixed", bisa typo
        'discount_value', // Double, bisa negatif
        'min_purchase', // Double, bisa negatif
        'max_discount', // Double, bisa negatif
        'max_usage',
        'usage_count', // Bisa diubah langsung, bypass validation
        'max_usage_per_user',
        'valid_from',
        'valid_until',
    ];
    
    // MASALAH: Boolean flag hell
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
    
    // MASALAH: Tidak ada method untuk:
    // - canBeRedeemedBy(User $user, Order $order)
    // - isActive()
    // - isUsedUp()
    // - calculateDiscount(Money $amount)
    // - meetsMinimumPurchase(Order $order)
    // - redeem(User $user, string $idempotencyKey)
    
    // MASALAH: Tidak ada validasi:
    // - code bisa empty atau format salah
    // - discount_value bisa negatif
    // - valid_until bisa sebelum valid_from
    // - usage_count bisa lebih dari max_usage
    // - discount_type bisa typo
    // - max_usage bisa 0 atau negatif
    
    // MASALAH: Tidak ada enforcement:
    // - Max usage per user
    // - Idempotency
    // - Pessimistic locking untuk race condition
    
    // MASALAH: Tidak ada immutability:
    // - code bisa diubah setelah dibuat
    // - max_usage bisa diubah setelah ada redemption
    // - usage_count bisa dikurangi
    
    // MASALAH: Kombinasi boolean invalid bisa terjadi:
    // - is_active=true tapi is_expired=true
    // - is_used_up=false tapi usage_count >= max_usage
    
    public function redemptions()
    {
        return $this->hasMany(VoucherRedemption::class);
    }
}
