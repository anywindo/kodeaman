<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('order_id')->constrained();
            $table->decimal('discount_applied', 10, 2);
            $table->string('idempotency_key')->unique();
            $table->string('ip_address')->nullable();
            $table->timestamp('redeemed_at');
            $table->timestamps();
        });

        // Trigger to prevent UPDATE
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::unprepared('
                CREATE TRIGGER prevent_voucher_redemptions_update
                BEFORE UPDATE ON voucher_redemptions
                BEGIN
                    SELECT RAISE(ABORT, \'Voucher redemption records are immutable and cannot be updated\');
                END;
            ');

            DB::unprepared('
                CREATE TRIGGER prevent_voucher_redemptions_delete
                BEFORE DELETE ON voucher_redemptions
                BEGIN
                    SELECT RAISE(ABORT, \'Voucher redemption records are immutable and cannot be deleted\');
                END;
            ');
        } else {
            DB::unprepared('
                CREATE TRIGGER prevent_voucher_redemptions_update
                BEFORE UPDATE ON voucher_redemptions
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Voucher redemption records are immutable and cannot be updated";
                END;
            ');

            DB::unprepared('
                CREATE TRIGGER prevent_voucher_redemptions_delete
                BEFORE DELETE ON voucher_redemptions
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Voucher redemption records are immutable and cannot be deleted";
                END;
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_voucher_redemptions_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_voucher_redemptions_delete');
        Schema::dropIfExists('voucher_redemptions');
    }
};
