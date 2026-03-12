<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * MODUL 1: User Model
 * 
 * MASALAH:
 * 1. Shallow model - hanya getter/setter, tidak ada business logic
 * 2. Tidak ada konsep lockout
 * 3. Tidak ada method untuk enforce security rules
 * 4. Semua field bisa diubah bebas (mass assignment)
 */
class User extends Authenticatable
{
    use Notifiable;

    // MASALAH: Semua field fillable, tidak ada protection
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    // MASALAH: Tidak ada method untuk:
    // - attemptLogin()
    // - lockAccount()
    // - isLocked()
    // - canAttemptLogin()
    
    // Model ini hanya data container, tidak ada domain logic
}
