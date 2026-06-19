<?php

namespace App\Listeners;

use App\Events\WalletDebited;
use App\Events\WalletSuspended;
use App\Models\WalletTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;

class DetectAnomalousActivity
{
    /**
     * Handle the event.
     */
    public function handle(WalletDebited $event): void
    {
        $wallet = $event->wallet;

        // Detect suspicious pattern: > 10 transactions in last 5 minutes
        $recentTransactionsCount = WalletTransaction::where('from_wallet_id', $wallet->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentTransactionsCount >= 10) {
            $wallet->suspend('Anomaly detected');
            event(new WalletSuspended($wallet, 'Anomaly detected'));
        }
    }
}
