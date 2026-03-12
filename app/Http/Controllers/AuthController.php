<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * MODUL 1: Authentication Controller
 * 
 * MASALAH YANG ADA:
 * 1. Tidak ada tracking login attempts - brute force attack possible
 * 2. Tidak ada lockout mechanism
 * 3. Session tidak ter-bind dengan device
 * 4. Validasi hanya di controller, tidak di domain model
 * 5. Tidak ada audit trail
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        // MASALAH: Tidak ada rate limiting, bisa brute force
        // MASALAH: Tidak ada logging untuk failed attempts
        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Invalid credentials'
            ]);
        }
        
        // MASALAH: Session tidak di-bind ke device fingerprint
        // Session bisa dicuri dan dipakai di device lain
        $request->session()->regenerate();
        
        return redirect()->intended('dashboard');
    }
    
    public function register(Request $request)
    {
        // MASALAH: Validasi hanya di controller
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
        
        // MASALAH: User model hanya anemic data container
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        Auth::login($user);
        
        return redirect('dashboard');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
