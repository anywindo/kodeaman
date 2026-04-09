<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    // Kisi-kisi hint: Gunakan $timestamps = false karena pakai attempted_at custom
    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'success',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    /**
     * Catat percobaan login GAGAL.
     *
     * Dipanggil saat Auth::attempt() return false.
     * Test: login_attempts_harus_tercatat_di_database
     * → assertDatabaseHas('login_attempts', ['email' => ..., 'success' => false])
     */
    public static function recordFailure(string $email, string $ip, ?string $userAgent = null): void
    {
        self::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'success' => false,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Catat percobaan login BERHASIL.
     *
     * Untuk audit trail — tahu kapan user terakhir login.
     */
    public static function recordSuccess(string $email, string $ip, ?string $userAgent = null): void
    {
        self::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'success' => true,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Cek apakah email ini harus di-lockout.
     *
     * Logika: apakah ada >= 5 percobaan GAGAL dalam 15 menit terakhir?
     *
     * Dari kisi-kisi: "cek apakah >= 5 attempts dalam 15 menit"
     * Dari test: login_gagal_5x_harus_lockout_15_menit
     */
    public static function shouldLockout(string $email): bool
    {
        $attempts = self::where('email', $email)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes(15))
            ->count();

        return $attempts >= 5;
    }

    /**
     * Hapus semua record gagal untuk email ini.
     *
     * Dipanggil setelah login berhasil.
     * Dari kisi-kisi: "clearAttempts(string $email): void - static method"
     */
    public static function clearAttempts(string $email): void
    {
        self::where('email', $email)
            ->where('success', false)
            ->delete();
    }
}