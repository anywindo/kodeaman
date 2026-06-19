<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Events\WalletTransferred;
use App\Exceptions\CannotTransferToSelfException;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

class WalletTransferService
{
    public function transfer(Wallet $from, Wallet $to, Money $amount): WalletTransaction
    {
        if ($from->id === $to->id) {
            throw new CannotTransferToSelfException();
        }

        return DB::transaction(function () use ($from, $to, $amount) {
            $from->debit($amount);
            $to->credit($amount);

            $transaction = WalletTransaction::create([
                'from_wallet_id' => $from->id,
                'to_wallet_id' => $to->id,
                'amount' => $amount->toCents(),
                'type' => TransactionType::TRANSFER,
                'status' => TransactionStatus::COMPLETED,
            ]);

            event(new WalletTransferred($from, $to, $amount));

            return $transaction;
        });
    }
}
