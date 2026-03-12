<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * MODUL 3: Test Cases untuk E-Wallet
 * 
 * Test ini akan GAGAL pada kode yang bermasalah.
 * Setelah diperbaiki, test harus PASS.
 */
class Modul3WalletTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function saldo_tidak_bisa_negatif()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100000,
        ]);

        $response = $this->actingAs($user)->postJson('/api/wallets/withdraw', [
            'wallet_id' => $wallet->id,
            'amount' => 150000, // Lebih besar dari saldo
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Insufficient balance'
        ]);

        // Saldo harus tetap
        $this->assertEquals(100000, $wallet->fresh()->balance);
    }

    /** @test */
    public function daily_limit_enforced()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 20000000, // 20 juta
        ]);

        // Withdraw 8 juta
        $this->actingAs($user)->postJson('/api/wallets/withdraw', [
            'wallet_id' => $wallet->id,
            'amount' => 8000000,
        ]);

        // Withdraw 3 juta lagi (total 11 juta, melebihi limit 10 juta)
        $response = $this->actingAs($user)->postJson('/api/wallets/withdraw', [
            'wallet_id' => $wallet->id,
            'amount' => 3000000,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Daily limit exceeded'
        ]);
    }

    /** @test */
    public function race_condition_prevented_dengan_locking()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000000,
        ]);

        // Simulasi 2 request bersamaan
        $promises = [];
        for ($i = 0; $i < 2; $i++) {
            $promises[] = $this->actingAs($user)->postJson('/api/wallets/withdraw', [
                'wallet_id' => $wallet->id,
                'amount' => 800000,
            ]);
        }

        // Salah satu harus gagal
        $successCount = 0;
        foreach ($promises as $response) {
            if ($response->status() === 200) {
                $successCount++;
            }
        }

        $this->assertEquals(1, $successCount);

        // Saldo harus 200000 (1000000 - 800000), bukan negatif
        $this->assertEquals(200000, $wallet->fresh()->balance);
    }

    /** @test */
    public function transfer_atomic_gagal_semua_atau_berhasil_semua()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $wallet1 = Wallet::factory()->create([
            'user_id' => $user1->id,
            'balance' => 1000000,
        ]);
        
        $wallet2 = Wallet::factory()->create([
            'user_id' => $user2->id,
            'balance' => 500000,
        ]);

        // Simulasi error di tengah transfer
        DB::shouldReceive('transaction')->andThrow(new \Exception('Database error'));

        try {
            $this->actingAs($user1)->postJson('/api/wallets/transfer', [
                'from_wallet_id' => $wallet1->id,
                'to_wallet_id' => $wallet2->id,
                'amount' => 300000,
            ]);
        } catch (\Exception $e) {
            // Expected
        }

        // Kedua saldo harus tetap (rollback)
        $this->assertEquals(1000000, $wallet1->fresh()->balance);
        $this->assertEquals(500000, $wallet2->fresh()->balance);
    }

    /** @test */
    public function tidak_bisa_transfer_ke_wallet_sendiri()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000000,
        ]);

        $response = $this->actingAs($user)->postJson('/api/wallets/transfer', [
            'from_wallet_id' => $wallet->id,
            'to_wallet_id' => $wallet->id, // Sama
            'amount' => 100000,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Cannot transfer to self'
        ]);
    }

    /** @test */
    public function transaction_log_immutable()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000000,
        ]);

        $this->actingAs($user)->postJson('/api/wallets/withdraw', [
            'wallet_id' => $wallet->id,
            'amount' => 100000,
        ]);

        $transaction = DB::table('wallet_transactions')->first();

        // Coba update transaction
        $this->expectException(\Exception::class);
        DB::table('wallet_transactions')
            ->where('id', $transaction->id)
            ->update(['amount' => 200000]);
    }

    /** @test */
    public function anomaly_detection_10_transaksi_dalam_5_menit_suspend()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 10000000,
        ]);

        // 11 transaksi dalam 5 menit
        for ($i = 0; $i < 11; $i++) {
            $this->actingAs($user)->postJson('/api/wallets/withdraw', [
                'wallet_id' => $wallet->id,
                'amount' => 10000,
            ]);
        }

        // Wallet harus suspended
        $this->assertTrue($wallet->fresh()->is_suspended);
        $this->assertEquals('Anomaly detected', $wallet->fresh()->suspended_reason);
    }

    /** @test */
    public function setiap_transaksi_tercatat_dengan_lengkap()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 1000000,
        ]);

        $this->actingAs($user)->postJson('/api/wallets/withdraw', [
            'wallet_id' => $wallet->id,
            'amount' => 100000,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'from_wallet_id' => $wallet->id,
            'amount' => 100000,
            'type' => 'withdrawal',
            'status' => 'completed',
        ]);
    }
}
