<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MODUL 2: Test Cases untuk Order & Refund
 * 
 * Test ini akan GAGAL pada kode yang bermasalah.
 * Setelah diperbaiki, test harus PASS.
 */
class Modul2OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_baru_hanya_bisa_status_pending()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'amount' => 100000,
            'status' => 'paid', // Coba langsung paid
        ]);

        $order = Order::first();
        
        // Harus tetap pending, tidak bisa langsung paid
        $this->assertEquals('pending', $order->status);
    }

    /** @test */
    public function tidak_bisa_ship_sebelum_paid()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/ship");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Cannot ship order that is not paid'
        ]);
    }

    /** @test */
    public function tidak_bisa_refund_sebelum_delivered()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid', // Baru paid, belum delivered
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/request-refund", [
                'reason' => 'Changed my mind'
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Can only refund delivered orders'
        ]);
    }

    /** @test */
    public function amount_tidak_bisa_negatif()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'amount' => -100000, // Negatif
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'errors' => [
                'amount' => ['Amount must be positive']
            ]
        ]);
    }

    /** @test */
    public function amount_tidak_bisa_diubah_setelah_order_dibuat()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'amount' => 100000,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/orders/{$order->id}/amount", [
                'amount' => 200000,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Amount is immutable'
        ]);

        // Amount harus tetap
        $this->assertEquals(100000, $order->fresh()->amount);
    }

    /** @test */
    public function refund_tidak_bisa_approved_tanpa_request_dulu()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
            'refund_requested_at' => null, // Belum request
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/approve-refund");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'No refund request found'
        ]);
    }

    /** @test */
    public function status_typo_tidak_bisa_disimpan()
    {
        $user = User::factory()->create();

        // Coba buat order dengan status typo
        $order = new Order([
            'user_id' => $user->id,
            'amount' => 100000,
            'status' => 'payed', // Typo
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $order->save();
    }

    /** @test */
    public function setiap_state_transition_tercatat_di_audit_log()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Confirm payment
        $order->confirmPayment();

        // Harus ada audit log
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'OrderPaid',
            'order_id' => $order->id,
        ]);
    }

    /** @test */
    public function order_flow_lengkap_harus_berurutan()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Flow: pending → paid → shipped → delivered → refund_requested → refunded

        $order->confirmPayment();
        $this->assertEquals('paid', $order->fresh()->status);

        $order->ship();
        $this->assertEquals('shipped', $order->fresh()->status);

        $order->confirmDelivery();
        $this->assertEquals('delivered', $order->fresh()->status);

        $order->requestRefund('Defective product');
        $this->assertEquals('refund_requested', $order->fresh()->status);

        $order->approveRefund();
        $this->assertEquals('refunded', $order->fresh()->status);
    }
}
