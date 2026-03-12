<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * MODUL 2: Order Controller
 * 
 * MASALAH:
 * 1. Anemic domain model - semua logic di controller
 * 2. Status bisa diubah langsung tanpa validasi urutan
 * 3. Amount bisa diubah setelah order dibuat
 * 4. Refund bisa di-approve tanpa request dulu
 * 5. Tidak ada audit trail
 * 6. Race condition possible
 */
class OrderController extends Controller
{
    public function create(Request $request)
    {
        // MASALAH: Tidak ada value object untuk Money
        // Amount bisa negatif
        $order = Order::create([
            'user_id' => auth()->id(),
            'amount' => $request->amount, // Bisa negatif!
            'status' => 'pending', // String bebas, bisa typo
            'order_date' => now(),
        ]);
        
        return response()->json($order);
    }
    
    public function pay($id)
    {
        $order = Order::find($id);
        
        // MASALAH: Langsung ubah status tanpa validasi state sebelumnya
        // Bisa paid berkali-kali
        $order->status = 'paid';
        $order->is_paid = true;
        $order->paid_at = now();
        $order->save();
        
        return response()->json($order);
    }
    
    public function ship($id)
    {
        $order = Order::find($id);
        
        // MASALAH: Bisa ship tanpa cek apakah sudah paid
        $order->status = 'shipped';
        $order->is_shipped = true;
        $order->shipped_at = now();
        $order->save();
        
        return response()->json($order);
    }
    
    public function deliver($id)
    {
        $order = Order::find($id);
        
        // MASALAH: Bisa deliver tanpa cek apakah sudah shipped
        $order->status = 'delivered';
        $order->is_delivered = true;
        $order->delivered_at = now();
        $order->save();
        
        return response()->json($order);
    }
    
    public function requestRefund(Request $request, $id)
    {
        $order = Order::find($id);
        
        // MASALAH: Bisa request refund meskipun belum delivered
        // Atau bahkan belum paid
        $order->refund_requested_at = now();
        $order->refund_reason = $request->reason;
        $order->save();
        
        return response()->json($order);
    }
    
    public function approveRefund($id)
    {
        $order = Order::find($id);
        
        // MASALAH: Bisa approve tanpa ada request dulu
        // refund_requested_at bisa null
        $order->status = 'refunded';
        $order->is_refunded = true;
        $order->is_refund_approved = true;
        $order->refunded_at = now();
        $order->save();
        
        // MASALAH: Tidak ada event/audit log
        
        return response()->json($order);
    }
    
    public function updateAmount(Request $request, $id)
    {
        $order = Order::find($id);
        
        // MASALAH: Amount bisa diubah setelah order dibuat
        // Bahkan setelah paid!
        $order->amount = $request->amount;
        $order->save();
        
        return response()->json($order);
    }
}
