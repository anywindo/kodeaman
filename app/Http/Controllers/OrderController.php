<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\ValueObjects\Money;
use Exception;
use InvalidArgumentException;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        try {
            $money = Money::fromCents($request->amount);
            
            $order = Order::create([
                'user_id' => auth()->id(),
                'amount' => $money->toCents(),
                'status' => OrderStatus::PENDING,
                'order_date' => now(),
            ]);
            
            return response()->json($order);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'errors' => [
                    'amount' => ['Amount must be positive']
                ]
            ], 422);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    public function pay($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->confirmPayment();
            return response()->json($order);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    public function ship($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->ship();
            return response()->json($order);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    public function deliver($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->confirmDelivery();
            return response()->json($order);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    public function requestRefund(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->requestRefund($request->reason);
            return response()->json($order);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    public function approveRefund($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->approveRefund();
            return response()->json($order);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    public function updateAmount(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->amount = $request->amount;
            $order->save();
            return response()->json($order);
        } catch (Exception $e) {
            return response()->json(['message' => 'Amount is immutable'], 422);
        }
    }
}
