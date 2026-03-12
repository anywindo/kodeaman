<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            
            // MASALAH: amount sebagai decimal, bisa negatif
            $table->decimal('amount', 10, 2);
            
            // MASALAH: status sebagai string, bisa typo
            $table->string('status');
            
            // MASALAH: Boolean flag hell
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_shipped')->default(false);
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_refunded')->default(false);
            $table->boolean('is_refund_approved')->default(false);
            
            $table->timestamp('order_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('refund_requested_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            $table->text('refund_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
