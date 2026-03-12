<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MODUL 1: Test Cases untuk Authentication
 * 
 * Test ini akan GAGAL pada kode yang bermasalah.
 * Setelah diperbaiki, test harus PASS.
 */
class Modul1AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_gagal_5x_harus_lockout_15_menit()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // Coba login gagal 5x
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // Login ke-6 harus ditolak meskipun password benar
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertStatus(429); // Too Many Requests
        $response->assertJson([
            'message' => 'Account locked. Try again in 15 minutes.'
        ]);
    }

    /** @test */
    public function login_attempts_harus_tercatat_di_database()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Harus ada record di login_attempts table
        $this->assertDatabaseHas('login_attempts', [
            'email' => 'test@example.com',
            'success' => false,
        ]);
    }

    /** @test */
    public function session_dari_ip_berbeda_harus_force_logout()
    {
        $user = User::factory()->create();

        // Login dari IP 1
        $response1 = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ], ['REMOTE_ADDR' => '192.168.1.1']);

        $token1 = $response1->json('token');

        // Login dari IP 2 dengan token yang sama
        $response2 = $this->withToken($token1)
            ->getJson('/api/user', ['REMOTE_ADDR' => '192.168.1.2']);

        // Harus ditolak karena IP berbeda
        $response2->assertStatus(401);
    }

    /** @test */
    public function setelah_lockout_expired_bisa_login_lagi()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Lock user
        $user->lockUntil(now()->addMinutes(15));

        // Travel time 16 menit
        $this->travel(16)->minutes();

        // Sekarang harus bisa login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function login_berhasil_harus_clear_login_attempts()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Gagal 3x
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrong',
            ]);
        }

        // Login berhasil
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Login attempts harus di-clear
        $this->assertEquals(0, $user->fresh()->failed_login_attempts);
    }
}
