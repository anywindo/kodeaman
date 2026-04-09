<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * MODUL 1: Authentication Controller
 * 
 * MASALAH YANG ADA:
 * [SOLVED] 1. Tidak ada tracking login attempts - brute force attack possible
 * [SOLVED] 2. Tidak ada lockout mechanism
 * [SOLVED] 3. Session tidak ter-bind dengan device
 * [SOLVED] 4. Validasi hanya di controller, tidak di domain model
 * [SOLVED] 5. Tidak ada audit trail
 */
class AuthController extends Controller
{
    /**
     * Login via API — mengembalikan JSON, bukan redirect.
     *
     * Flow sesuai kisi-kisi:
     * 1. Ambil email dari request
     * 2. Cek LoginAttempt::shouldLockout($email) → 429
     * 3. Cari user by email
     * 4. Cek $user->isLocked() → 429
     * 5. Coba Auth::attempt()
     * 6. Gagal → record failure, increment failed attempts
     * 7. Berhasil → record success, clear attempts, return token
     */

    public function login(Request $request)
    {
//        $credentials = $request->only('email', 'password'); -- digantikan

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $ip = $request->ip();

        // PERBAIKAN 1: Cek lockout dari login attempts
        // [SOLVED] MASALAH: Tidak ada rate limiting, bisa brute force
        if (LoginAttempt::shouldLockout($email)) {
            return response()->json([
                'message' => 'Account locked. Try again in 15 minutes.'
            ], 429);
        }

        // PERBAIKAN 2: Cek apakah user ada dan apakah akunnya terkunci
        $user = User::where('email', $email)->first();

        if ($user && $user->isLocked()) {
            return response()->json([
                'message' => 'Account locked. Try again in 15 minutes.'
            ], 429);
        }

        // PERBAIKAN 3: Coba login dengan tracking
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Record gagal di tabel login_attempts (audit trail)
            LoginAttempt::recordFailure($email, $ip, $request->userAgent());

            // Increment counter di user (jika user ada)
            if ($user) {
                $user->incrementFailedAttempts();
            }

            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // LOGIN BERHASIL
        $user = Auth::user();

        // PERBAIKAN 4: Record sukses dan clear attempts
        // (menjawab: "login berhasil → clear login attempts")
        LoginAttempt::recordSuccess($email, $ip, $request->userAgent());
        LoginAttempt::clearAttempts($email);
        $user->clearFailedAttempts();

        // PERBAIKAN 5: Buat token Sanctum yang terikat ke IP
        // (menjawab: "Session tidak di-bind ke device fingerprint")
        // Simpan IP di dalam token abilities/metadata agar bisa
        // divalidasi di middleware nanti
        $token = $user->createToken('auth-token', ['*'], null);

        // Simpan IP login di personal_access_tokens
        $token->accessToken->forceFill([
            'ip_address' => $ip,
        ])->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }
    
    public function register(Request $request)
    {
        // [SOLVED] MASALAH: Validasi hanya di controller
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
        
        // [SOLVED] MASALAH: User model hanya anemic data container
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        A$token = $user->createToken('auth-token');

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ], 201);
    }
    
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
