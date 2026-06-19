<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletTransferService;
use App\ValueObjects\Money;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function deposit(Request $request)
    {
        $wallet = Wallet::findOrFail($request->wallet_id);
        $amount = Money::fromCents($request->amount);

        $wallet->credit($amount);

        WalletTransaction::create([
            'to_wallet_id' => $wallet->id,
            'amount' => $amount->toCents(),
            'type' => TransactionType::DEPOSIT,
            'status' => TransactionStatus::COMPLETED,
        ]);

        return response()->json($wallet->fresh());
    }

    public function withdraw(Request $request)
    {
        $wallet = Wallet::findOrFail($request->wallet_id);
        $amount = Money::fromCents($request->amount);

        $wallet->debit($amount);

        WalletTransaction::create([
            'from_wallet_id' => $wallet->id,
            'amount' => $amount->toCents(),
            'type' => TransactionType::WITHDRAWAL,
            'status' => TransactionStatus::COMPLETED,
        ]);

        return response()->json($wallet->fresh());
    }

    public function transfer(Request $request, WalletTransferService $transferService)
    {
        $fromWallet = Wallet::findOrFail($request->from_wallet_id);
        $toWallet = Wallet::findOrFail($request->to_wallet_id);
        $amount = Money::fromCents($request->amount);

        $transaction = $transferService->transfer($fromWallet, $toWallet, $amount);

        return response()->json([
            'from' => $fromWallet->fresh(),
            'to' => $toWallet->fresh(),
            'transaction' => $transaction,
        ]);
    }

    public function getBalance($id)
    {
        $wallet = Wallet::findOrFail($id);
        return response()->json(['balance' => $wallet->balance]);
    }
}
