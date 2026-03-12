<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

/**
 * MODUL 3: Wallet Controller
 * 
 * MASALAH:
 * 1. Saldo bisa negatif
 * 2. Tidak ada daily limit
 * 3. Race condition - double spending possible
 * 4. Tidak ada transaction log
 * 5. Transfer tidak atomic
 * 6. God object - terlalu banyak tanggung jawab
 */
class WalletController extends Controller
{
    public function deposit(Request $request)
    {
        $wallet = Wallet::find($request->wallet_id);
        
        // MASALAH: Langsung ubah balance tanpa validasi
        // Tidak ada value object Money
        $wallet->balance += $request->amount;
        $wallet->save();
        
        // MASALAH: Tidak ada transaction log
        // MASALAH: Tidak ada event untuk audit
        
        return response()->json($wallet);
    }
    
    public function withdraw(Request $request)
    {
        $wallet = Wallet::find($request->wallet_id);
        
        // MASALAH: Tidak ada validasi saldo cukup
        // Bisa jadi negatif!
        $wallet->balance -= $request->amount;
        $wallet->save();
        
        // MASALAH: Tidak ada daily limit check
        // User bisa withdraw unlimited
        
        return response()->json($wallet);
    }
    
    public function transfer(Request $request)
    {
        $fromWallet = Wallet::find($request->from_wallet_id);
        $toWallet = Wallet::find($request->to_wallet_id);
        
        // MASALAH: Tidak ada validasi transfer ke diri sendiri
        // MASALAH: Tidak atomic - bisa gagal di tengah
        // MASALAH: Race condition - dua request bersamaan bisa bikin saldo negatif
        
        $fromWallet->balance -= $request->amount;
        $fromWallet->save();
        
        // Jika gagal di sini, uang hilang!
        $toWallet->balance += $request->amount;
        $toWallet->save();
        
        // MASALAH: Tidak ada transaction log
        
        return response()->json([
            'from' => $fromWallet,
            'to' => $toWallet,
        ]);
    }
    
    public function getBalance($id)
    {
        $wallet = Wallet::find($id);
        return response()->json(['balance' => $wallet->balance]);
    }
    
    // MASALAH: Controller jadi God object
    // Seharusnya ada service terpisah untuk:
    // - WalletTransferService
    // - WalletNotificationService
    // - WalletReportService
    
    public function sendNotification($id)
    {
        // Notification logic di controller
        $wallet = Wallet::find($id);
        // ...
    }
    
    public function generateReport($id)
    {
        // Report logic di controller
        $wallet = Wallet::find($id);
        // ...
    }
    
    public function detectFraud($id)
    {
        // Fraud detection di controller
        $wallet = Wallet::find($id);
        // ...
    }
}
