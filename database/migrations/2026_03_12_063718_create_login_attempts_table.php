<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Buat login attempts table buat lookup user

return new class extends Migration
{
    public function up()
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->boolean('success')->default(false);
            $table->timestamp('attempted_at')->useCurrent();

            // Index gabungan untuk query cepat:
            // "berapa kali email X gagal login dalam 15 menit terakhir?"
            $table->index(['email', 'attempted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_attempts');
    }
};