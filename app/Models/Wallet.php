<?php

namespace App\Models;

use App\Events\WalletCredited;
use App\Events\WalletDebited;
use App\Exceptions\DailyLimitExceededException;
use App\Exceptions\InsufficientBalanceException;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    use HasFactory;
    
    private const DAILY_LIMIT = 10000000; // 10 million (in whatever unit `balance` uses, usually cents, but tests might use it as raw units, let's assume it's cents)

    protected $fillable = [
        'user_id',
        // balance removed from fillable to enforce domain methods
    ];

    protected $casts = [
        'is_suspended' => 'boolean',
        'daily_spent_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function debit(Money $amount): void
    {
        if (!$this->canDebit($amount)) {
            throw new InsufficientBalanceException();
        }

        if ($this->exceedsDailyLimit($amount)) {
            throw new DailyLimitExceededException();
        }

        DB::transaction(function () use ($amount) {
            $this->lockForUpdate();

            $this->balance -= $amount->toCents();
            $this->updateDailySpent($amount);
            $this->save();

            event(new WalletDebited($this, $amount));
        });
    }

    public function credit(Money $amount): void
    {
        DB::transaction(function () use ($amount) {
            $this->lockForUpdate();

            $this->balance += $amount->toCents();
            $this->save();

            event(new WalletCredited($this, $amount));
        });
    }

    private function canDebit(Money $amount): bool
    {
        $currentBalance = Money::fromCents($this->balance ?? 0);
        return $currentBalance->isGreaterThanOrEqual($amount);
    }

    private function exceedsDailyLimit(Money $amount): bool
    {
        $today = now()->toDateString();
        $dailySpent = ($this->daily_spent_date && $this->daily_spent_date->toDateString() === $today) 
            ? Money::fromCents($this->daily_spent ?? 0) 
            : Money::fromCents(0);

        return $dailySpent->add($amount)->isGreaterThan(
            Money::fromCents(self::DAILY_LIMIT)
        );
    }

    private function updateDailySpent(Money $amount): void
    {
        $today = now()->toDateString();
        if (!$this->daily_spent_date || $this->daily_spent_date->toDateString() !== $today) {
            $this->daily_spent = 0;
            $this->daily_spent_date = $today;
        }

        $this->daily_spent += $amount->toCents();
    }

    public function suspend(string $reason): void
    {
        $this->is_suspended = true;
        $this->suspended_reason = $reason;
        $this->suspended_at = now();
        $this->save();
    }
}
