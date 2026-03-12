<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            
            // MASALAH: discount_type string bebas, bisa typo
            $table->string('discount_type');
            
            // MASALAH: Semua bisa negatif
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_purchase', 10, 2)->default(0);
            $table->decimal('max_discount', 10, 2)->nullable();
            
            $table->integer('max_usage')->default(1);
            $table->integer('usage_count')->default(0);
            
            // MASALAH: Boolean flag hell
            $table->boolean('is_active')->default(true);
            $table->boolean('is_expired')->default(false);
            $table->boolean('is_used_up')->default(false);
            $table->boolean('is_first_order_only')->default(false);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_reusable')->default(false);
            
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};
