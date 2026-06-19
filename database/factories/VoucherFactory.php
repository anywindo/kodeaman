<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Voucher;

class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->lexify('PROMO????')),
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_purchase' => 0,
            'max_discount' => null,
            'max_usage' => 100,
            'usage_count' => 0,
            'is_active' => true,
            'is_expired' => false,
            'is_used_up' => false,
            'is_first_order_only' => false,
            'is_stackable' => false,
            'is_reusable' => false,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
        ];
    }
}
