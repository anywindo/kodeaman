<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private function __construct(private int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromRupiah(float $rupiah): self
    {
        return new self((int) round($rupiah * 100));
    }

    public function toCents(): int
    {
        return $this->cents;
    }

    public function toRupiah(): float
    {
        return $this->cents / 100;
    }

    public function isGreaterThan(Money $other): bool
    {
        return $this->cents > $other->cents;
    }
}
