<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\VoucherRedemptionService;
use App\ValueObjects\VoucherCode;
use App\Exceptions\VoucherCannotBeRedeemedException;
use InvalidArgumentException;

class VoucherController extends Controller
{
    private VoucherRedemptionService $service;

    public function __construct(VoucherRedemptionService $service)
    {
        $this->service = $service;
    }

    public function redeem(Request $request)
    {
        try {
            $code = VoucherCode::fromString($request->code);
            $order = Order::findOrFail($request->order_id);
            $user = $request->user();

            $redemption = $this->service->redeemVoucher($code, $user, $order, $request->idempotency_key);

            return response()->json([
                'message' => 'Voucher applied',
                'discount' => $redemption->discount_applied
            ]);

        } catch (VoucherCannotBeRedeemedException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    public function apply(Request $request)
    {
        // ... Tidak begitu relevan untuk direfactor seluruhnya jika bukan bagian inti test
        $voucher = Voucher::where('code', strtoupper(trim($request->code)))->first();
        if (!$voucher) {
            return response()->json(['error' => 'Not found'], 404);
        }
        
        $discount = $voucher->discount_value;
        
        return response()->json(['discount' => $discount]);
    }
    
    public function create(Request $request)
    {
        try {
            $voucher = Voucher::create([
                'code' => $request->code,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'min_purchase' => $request->min_purchase,
                'max_discount' => $request->max_discount,
                'max_usage' => $request->max_usage,
                'valid_from' => $request->valid_from,
                'valid_until' => $request->valid_until,
            ]);
            
            return response()->json($voucher);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    public function checkUsage($code)
    {
        try {
            $codeVO = VoucherCode::fromString($code);
            $voucher = Voucher::where('code', $codeVO->toString())->first();
            
            if (!$voucher) {
                return response()->json(['error' => 'Voucher not found'], 404);
            }
            
            return response()->json([
                'code' => $voucher->code,
                'usage_count' => $voucher->usage_count,
                'max_usage' => $voucher->max_usage,
                'remaining' => $voucher->max_usage - $voucher->usage_count,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid code'], 400);
        }
    }
}
