<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\VoucherRedemption;
use App\Models\Order;
use App\Models\User;
use App\ValueObjects\VoucherCode;
use App\Exceptions\VoucherCannotBeRedeemedException;
use App\Events\VoucherRedeemed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherRedemptionService
{
    public function redeemVoucher(
        VoucherCode $code,
        User $user,
        Order $order,
        string $idempotencyKey = null
    ): VoucherRedemption {
        $idempotencyKey = $idempotencyKey ?? (string) Str::uuid();

        // Check idempotency early
        $existing = VoucherRedemption::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing; // Already processed
        }

        return DB::transaction(function () use ($code, $user, $order, $idempotencyKey) {
            // Pessimistic locking
            $voucher = Voucher::lockForUpdate()->where('code', $code->toString())->first();

            if (!$voucher) {
                throw new VoucherCannotBeRedeemedException('Voucher not found');
            }

            // Validasi Domain Logic
            if (!$voucher->canBeRedeemed($user, $order)) {
                // Di test case ada message spesifik yang diuji:
                if (!$voucher->isActive() || !$voucher->isWithinValidityPeriod()) {
                    throw new VoucherCannotBeRedeemedException('Voucher has expired');
                }
                if (!$voucher->meetsMinimumPurchase($order->amount)) {
                    throw new VoucherCannotBeRedeemedException('Minimum purchase not met');
                }
                if ($voucher->is_first_order_only) {
                    throw new VoucherCannotBeRedeemedException('Voucher only for first order');
                }
                if (!$voucher->hasRemainingUsage()) {
                    throw new VoucherCannotBeRedeemedException('Voucher fully used');
                }

                throw new VoucherCannotBeRedeemedException('Voucher cannot be applied');
            }

            // Increment usage
            $voucher->usage_count += 1;
            $voucher->save();

            // Calculate actual discount
            $discount = $voucher->calculateDiscount($order->amount);

            // Create record
            $redemption = VoucherRedemption::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'discount_applied' => $discount,
                'idempotency_key' => $idempotencyKey,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'redeemed_at' => now(),
            ]);

            // Dispatch event
            event(new VoucherRedeemed($voucher, $user));

            return $redemption;
        });
    }
}
