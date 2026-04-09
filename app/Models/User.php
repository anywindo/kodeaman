<?php

namespace App\Models;

use Carbon\Carbon; // date and time requirement
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Tambahin sanctum soalnya di req. ada

/**
 * MODUL 1: User Model
 * 
 * MASALAH:
 * [SOLVED] 1. Shallow model - hanya getter/setter, tidak ada business logic
 * [SOLVED] 2. Tidak ada konsep lockout
 * [SOLVED] 3. Tidak ada method untuk enforce security rules
 * [SOLVED] 4. Semua field bisa diubah bebas (mass assignment)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // [SOLVED] MASALAH: Semua field fillable, tidak ada protection
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
        // PERBAIKAN: Cast locked_until ke datetime
        // agar bisa dibandingkan dengan now()
        'locked_until' => 'datetime',
    ];
    
    // [SOLVED] MASALAH: Tidak ada method untuk:
    // - attemptLogin()
    // - lockAccount()
    // - isLocked()
    // - canAttemptLogin()
    
    // Model ini hanya data container, tidak ada domain logic

    /**
     * Cek apakah akun sedang terkunci.
     *
     * Dari komentar: "Tidak ada konsep lockout"
     * Test: setelah_lockout_expired_bisa_login_lagi
     * → lock 15 menit, travel 16 menit, harus bisa login
     *
     * Artinya: locked jika locked_until masih di MASA DEPAN.
     */
    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Kunci akun sampai waktu tertentu.
     *
     * Test memanggil: $user->lockUntil(now()->addMinutes(15))
     */
    public function lockUntil(Carbon $until): void
    {
        $this->locked_until = $until;
        $this->save();
    }

    /**
     * Buka kunci akun dan reset counter.
     */
    public function unlock(): void
    {
        $this->locked_until = null;
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Cek apakah user boleh coba login.
     *
     * Dari kisi-kisi: "return !$this->isLocked()"
     */
    public function canAttemptLogin(): bool
    {
        return !$this->isLocked();
    }

    /**
     * Tambah counter gagal login, lock jika sudah >= 5.
     *
     * Dari kisi-kisi: "increment counter, lock jika >= 5"
     * Test: login_gagal_5x_harus_lockout_15_menit
     */
    public function incrementFailedAttempts(): void
    {
        $this->failed_login_attempts++;

        if ($this->failed_login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(15);
        }

        $this->save();
    }

    /**
     * Reset counter gagal login ke 0.
     *
     * Test: login_berhasil_harus_clear_login_attempts
     * → assertequals(0, $user->fresh()->failed_login_attempts)
     */
    public function clearFailedAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->save();
    }
}
