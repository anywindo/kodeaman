<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            
            // MASALAH: balance sebagai decimal, bisa negatif
            $table->decimal('balance', 15, 2)->default(0);
            
            $table->timestamps();
            
            // MASALAH: Tidak ada field untuk:
            // $table->decimal('daily_spent', 15, 2)->default(0);
            // $table->date('daily_spent_date')->nullable();
            // $table->boolean('is_suspended')->default(false);
            // $table->string('suspended_reason')->nullable();
            // $table->timestamp('suspended_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallets');
    }
};
