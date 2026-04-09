<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateTokenIp
{
    /**
     * Cek apakah IP request sama dengan IP saat token dibuat.
     *
     * Menjawab masalah: "Session bisa dicuri dan dipakai di device lain"
     * Test: session_dari_ip_berbeda_harus_force_logout
     * → Login dari IP 192.168.1.1, akses dari 192.168.1.2 → 401
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && $token->ip_address && $token->ip_address !== $request->ip()) {
            // IP berbeda — kemungkinan session hijacking
            // Revoke token untuk keamanan
            $token->delete();

            return response()->json([
                'message' => 'Session invalid. IP mismatch detected.'
            ], 401);
        }

        return $next($request);
    }
}