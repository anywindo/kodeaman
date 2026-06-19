<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\ImmutableRecordException;

class VoucherRedemption extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function () {
            throw new ImmutableRecordException('Redemption records cannot be modified');
        });

        static::deleting(function () {
            throw new ImmutableRecordException('Redemption records cannot be deleted');
        });
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
