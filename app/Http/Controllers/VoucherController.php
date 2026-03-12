<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * MODUL 4: Voucher Controller
 * 
 * MASALAH:
 * 1. Race condition - double redemption possible
 * 2. Tidak ada validasi domain rules
 * 3. Primitive obsession
 * 4. Tidak ada audit trail
 * 5. Invalid state bisa terjadi
 */
class VoucherController extends Controller
{
    public function redeem(Request $request)
    {
        $voucher = Voucher::where('code', $request->code)->first();
        $order = Order::find($request->order_id);
        
        // MASALAH: Tidak ada idempotency key
        // Jika client retry request, bisa double redeem!
        
        // MASALAH: Race condition - dua request bersamaan bisa redeem voucher yang sama
        // Tidak ada locking
        if ($voucher->usage_count < $voucher->max_usage) {
            $voucher->usage_count++;
            $voucher->save();
            
            // MASALAH: Tidak ada validasi:
            // - Voucher expired?
            // - Min purchase terpenuhi?
            // - User eligible?
            // - Max usage per user?
            
            $discount = $voucher->discount_value;
            $order->discount = $discount;
            $order->save();
            
            // MASALAH: Tidak ada transaction log
            // MASALAH: Tidak ada event untuk audit
            // MASALAH: Tidak ada anomaly detection
            
            return response()->json([
                'message' => 'Voucher applied',
                'discount' => $discount
            ]);
        }
        
        return response()->json(['message' => 'Voucher not available'], 400);
    }
    
    public function apply(Request $request)
    {
        $voucher = Voucher::where('code', $request->code)->first();
        
        // MASALAH: Tidak ada validasi apapun
        // Langsung apply discount
        $discount = $voucher->discount_value;
        
        return response()->json(['discount' => $discount]);
    }
    
    public function create(Request $request)
    {
        // MASALAH: Tidak ada validasi domain rules
        $voucher = Voucher::create([
            'code' => $request->code, // Tidak di-normalize (uppercase, trim)
            'discount_type' => $request->discount_type, // String bebas
            'discount_value' => $request->discount_value, // Bisa negatif
            'min_purchase' => $request->min_purchase, // Bisa negatif
            'max_discount' => $request->max_discount, // Bisa negatif
            'max_usage' => $request->max_usage, // Bisa 0 atau negatif
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until, // Bisa sebelum valid_from
        ]);
        
        return response()->json($voucher);
    }
    
    public function checkUsage($code)
    {
        $voucher = Voucher::where('code', $code)->first();
        
        // MASALAH: Tidak ada protection dari enumeration attack
        // Attacker bisa brute force voucher codes
        // MASALAH: Expose internal data (usage_count, max_usage)
        
        if (!$voucher) {
            return response()->json(['error' => 'Voucher not found'], 404);
        }
        
        return response()->json([
            'code' => $voucher->code,
            'usage_count' => $voucher->usage_count,
            'max_usage' => $voucher->max_usage,
            'remaining' => $voucher->max_usage - $voucher->usage_count,
        ]);
    }
}
