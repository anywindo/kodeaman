<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\ImmutableRecordException;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'from_wallet_id',
        'to_wallet_id',
        'amount',
        'type',
        'status',
        'metadata',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'metadata' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::updating(function () {
            throw new ImmutableRecordException();
        });
    }

    public function fromWallet()
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    public function toWallet()
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }
}
