<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;

class AuditLog extends Model
{
    protected $guarded = ['id'];
    
    protected $casts = [
        'data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::updating(function () {
            throw new Exception('Audit logs are immutable and cannot be updated.');
        });
        
        static::deleting(function () {
            throw new Exception('Audit logs are immutable and cannot be deleted.');
        });
    }
}
