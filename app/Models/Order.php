<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODUL 2: Order Model
 * 
 * MASALAH:
 * 1. Primitive obsession - status string, amount double, user_id int
 * 2. Boolean flag hell - banyak boolean untuk state
 * 3. Anemic model - tidak ada business logic
 * 4. Semua field bisa diubah (tidak ada immutability)
 * 5. Invalid state bisa direpresentasikan
 */
class Order extends Model
{
    // MASALAH: Semua field fillable, tidak ada protection
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'order_date',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'refund_requested_at',
        'refunded_at',
        'refund_reason',
        'refund_amount',
    ];
    
    // MASALAH: Boolean flag hell
    // Kombinasi invalid bisa terjadi:
    // - is_refunded=true tapi is_paid=false
    // - is_delivered=true tapi is_shipped=false
    protected $casts = [
        'is_paid' => 'boolean',
        'is_shipped' => 'boolean',
        'is_delivered' => 'boolean',
        'is_refunded' => 'boolean',
        'is_refund_approved' => 'boolean',
        'order_date' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];
    
    // MASALAH: Tidak ada method untuk:
    // - confirmPayment()
    // - ship()
    // - confirmDelivery()
    // - requestRefund()
    // - approveRefund()
    
    // MASALAH: Tidak ada validasi:
    // - amount bisa negatif
    // - status bisa typo: "payed", "PAID", "Paid"
    // - refund_date bisa sebelum order_date
    // - refund_amount bisa lebih besar dari amount
    
    // MASALAH: Tidak ada enforcement temporal coupling:
    // - Bisa langsung refunded tanpa melalui paid → shipped → delivered
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
