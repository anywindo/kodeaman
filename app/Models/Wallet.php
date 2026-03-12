<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODUL 3: Wallet Model
 * 
 * MASALAH:
 * 1. Anemic model - hanya data container
 * 2. Tidak ada domain rules (saldo negatif, daily limit)
 * 3. Tidak ada value object untuk Money
 * 4. God object - terlalu banyak tanggung jawab
 * 5. Tidak ada protection dari race condition
 */
class Wallet extends Model
{
    // MASALAH: Semua field fillable
    protected $fillable = [
        'user_id',
        'balance',
    ];
    
    protected $casts = [
        'balance' => 'decimal:2',
    ];
    
    // MASALAH: Tidak ada field untuk:
    // - daily_limit
    // - daily_spent
    // - is_suspended
    // - suspended_reason
    
    // MASALAH: Tidak ada method untuk:
    // - debit(Money $amount)
    // - credit(Money $amount)
    // - canDebit(Money $amount)
    // - exceedsDailyLimit(Money $amount)
    // - suspend(string $reason)
    
    // MASALAH: Tidak ada validasi:
    // - balance bisa negatif
    // - tidak ada daily limit enforcement
    
    // MASALAH: God object - semua logic di satu class
    // Seharusnya ada separation:
    // - Wallet (domain logic)
    // - WalletTransferService (transfer logic)
    // - WalletNotificationService (notification)
    // - WalletReportService (reporting)
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // MASALAH: Method ini seharusnya di service terpisah
    public function withdraw($amount)
    {
        $this->balance -= $amount;
        $this->save();
    }
    
    public function deposit($amount)
    {
        $this->balance += $amount;
        $this->save();
    }
    
    public function transfer($toWallet, $amount)
    {
        $this->balance -= $amount;
        $toWallet->balance += $amount;
        $this->save();
        $toWallet->save();
    }
    
    // MASALAH: Notification logic di model
    public function sendNotification($message)
    {
        // ...
    }
    
    // MASALAH: Report logic di model
    public function generateReport()
    {
        // ...
    }
    
    // MASALAH: Fraud detection di model
    public function detectFraud()
    {
        // ...
    }
}
