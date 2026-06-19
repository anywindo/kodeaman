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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_wallet_id')->nullable()->constrained('wallets');
            $table->foreignId('to_wallet_id')->nullable()->constrained('wallets');
            $table->bigInteger('amount');
            $table->string('type');
            $table->string('status');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Add database trigger to prevent updates.
        // For SQLite (used in testing):
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared("
                CREATE TRIGGER prevent_wallet_transaction_update 
                BEFORE UPDATE ON wallet_transactions 
                BEGIN 
                    SELECT RAISE(ABORT, 'Wallet transactions are immutable'); 
                END;
            ");
        } elseif (DB::getDriverName() === 'mysql') {
            DB::unprepared("
                CREATE TRIGGER prevent_wallet_transaction_update
                BEFORE UPDATE ON wallet_transactions
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Wallet transactions are immutable';
                END;
            ");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION prevent_update()
                RETURNS TRIGGER AS $$
                BEGIN
                    RAISE EXCEPTION 'Wallet transactions are immutable';
                END;
                $$ LANGUAGE plpgsql;

                CREATE TRIGGER prevent_wallet_transaction_update
                BEFORE UPDATE ON wallet_transactions
                FOR EACH ROW EXECUTE FUNCTION prevent_update();
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_wallet_transaction_update');
        } elseif (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_wallet_transaction_update');
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_wallet_transaction_update');
            DB::unprepared('DROP FUNCTION IF EXISTS prevent_update');
        }

        Schema::dropIfExists('wallet_transactions');
    }
};
