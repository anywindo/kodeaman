<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Voucher;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * MODUL 4: Test Cases untuk Voucher & Promo
 */
class Modul4VoucherTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function voucher_tidak_bisa_dipakai_lebih_dari_max_usage()
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create([
            'code' => 'PROMO10',
            'max_usage' => 5,
            'usage_count' => 0,
        ]);

        // Simulasi 10 request bersamaan (race condition)
        $promises = [];
        for ($i = 0; $i < 10; $i++) {
            $order = Order::factory()->create(['user_id' => $user->id]);
            $promises[] = $this->actingAs($user)->postJson('/api/vouchers/redeem', [
                'code' => 'PROMO10',
                'order_id' => $order->id,
            ]);
        }

        // Hanya 5 yang berhasil
        $successCount = 0;
        foreach ($promises as $response) {
            if ($response->status() === 200) {
                $successCount++;
            }
        }

        $this->assertEquals(5, $successCount);
        $this->assertEquals(5, $voucher->fresh()->usage_count);
    }

    /** @test */
    public function discount_value_tidak_bisa_negatif()
    {
        $this->expectException(\InvalidArgumentException::class);

        Voucher::create([
            'code' => 'INVALID',
            'discount_type' => 'fixed',
            'discount_value' => -10000, // Negatif
            'valid_from' => now(),
            'valid_until' => now()->addDays(7),
        ]);
    }

    /** @test */
    public function valid_until_tidak_bisa_sebelum_valid_from()
    {
        $this->expectException(\InvalidArgumentException::class);

        Voucher::create([
            'code' => 'INVALID',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'valid_from' => now()->addDays(7),
            'valid_until' => now(), // Sebelum valid_from
        ]);
    }

    /** @test */
    public function voucher_expired_tidak_bisa_dipakai()
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create([
            'code' => 'EXPIRED',
            'valid_from' => now()->subDays(7),
            'valid_until' => now()->subDay(), // Sudah expired
        ]);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/vouchers/redeem', [
            'code' => 'EXPIRED',
            'order_id' => $order->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Voucher has expired']);
    }

    /** @test */
    public function min_purchase_harus_terpenuhi()
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create([
            'code' => 'MINPURCHASE',
            'min_purchase' => 100000,
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'amount' => 50000, // Kurang dari min purchase
        ]);

        $response = $this->actingAs($user)->postJson('/api/vouchers/redeem', [
            'code' => 'MINPURCHASE',
            'order_id' => $order->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Minimum purchase not met']);
    }

    /** @test */
    public function first_order_only_voucher_hanya_bisa_dipakai_sekali()
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create([
            'code' => 'FIRSTORDER',
            'is_first_order_only' => true,
        ]);

        // Order pertama
        $order1 = Order::factory()->create(['user_id' => $user->id]);
        $response1 = $this->actingAs($user)->postJson('/api/vouchers/redeem', [
            'code' => 'FIRSTORDER',
            'order_id' => $order1->id,
        ]);
        $response1->assertStatus(200);

        // Order kedua
        $order2 = Order::factory()->create(['user_id' => $user->id]);
        $response2 = $this->actingAs($user)->postJson('/api/vouchers/redeem', [
            'code' => 'FIRSTORDER',
            'order_id' => $order2->id,
        ]);
        $response2->assertStatus(422);
        $response2->assertJson(['message' => 'Voucher only for first order']);
    }

    /** @test */
    public function max_discount_cap_diterapkan()
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create([
            'code' => 'MAXCAP',
            'discount_type' => 'percentage',
            'discount_value' => 50, // 50%
            'max_discount' => 50000, // Max 50rb
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'amount' => 200000, // 50% = 100rb, tapi max 50rb
        ]);

        $response = $this->actingAs($user)->postJson('/api/vouchers/redeem', [
            'code' => 'MAXCAP',
            'order_id' => $order->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['discount' => 50000]); // Bukan 100000
    }

    /** @test */
    public function redemption_log_immutable()
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create(['code' => 'TEST']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->postJson('/api/vouchers/redeem', [
            'code' => 'TEST',
            'order_id' => $order->id,
        ]);

        $redemption = DB::table('voucher_redemptions')->first();

        // Coba update
        $this->expectException(\Exception::class);
        DB::table('voucher_redemptions')
            ->where('id', $redemption->id)
            ->update(['discount_amount' => 999999]);
    }

    /** @test */
    public function abuse_detection_5_redemption_dalam_1_jam()
    {
        $user = User::factory()->create();

        // 6 redemption dalam 1 jam
        for ($i = 0; $i < 6; $i++) {
            $voucher = Voucher::factory()->create(['code' => "PROMO{$i}"]);
            $order = Order::factory()->create(['user_id' => $user->id]);
            
            $this->actingAs($user)->postJson('/api/vouchers/redeem', [
                'code' => "PROMO{$i}",
                'order_id' => $order->id,
            ]);
        }

        // Harus ada event SuspiciousVoucherActivity
        Event::assertDispatched(SuspiciousVoucherActivity::class);
    }
}
