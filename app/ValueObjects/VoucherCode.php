<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final class VoucherCode
{
    private string $code;

    private function __construct(string $code)
    {
        if (empty($code)) {
            throw new InvalidArgumentException('Voucher code cannot be empty');
        }
        
        // Aturan: Hanya boleh huruf kapital dan angka, minimal 3, maksimal 20 (bisa disesuaikan)
        if (!preg_match('/^[A-Z0-9]+$/', $code)) {
            throw new InvalidArgumentException('Invalid voucher code format');
        }

        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        $normalized = strtoupper(trim($code));
        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->code;
    }

    public function equals(VoucherCode $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
