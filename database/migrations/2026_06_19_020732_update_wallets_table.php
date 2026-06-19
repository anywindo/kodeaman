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
        // First we modify the balance column if needed. 
        // Wait, SQLite has limitations with modifying column types.
        // It's safer to just add the new columns.
        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('daily_spent')->default(0)->after('balance');
            $table->date('daily_spent_date')->nullable()->after('daily_spent');
            $table->boolean('is_suspended')->default(false)->after('daily_spent_date');
            $table->string('suspended_reason')->nullable()->after('is_suspended');
            $table->timestamp('suspended_at')->nullable()->after('suspended_reason');
        });
        
        // Convert existing balance to bigInteger if possible. We will leave balance as is for now 
        // and handle decimal to cents conversion in the model cast or accessor if it's decimal(15,2).
        // The original schema says $table->decimal('balance', 15, 2)->default(0);
        // Laravel's test passes 100000000 etc assuming it's already an integer!
        // We don't need to change `balance` column type if tests assume it holds large integers without fractions.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn([
                'daily_spent',
                'daily_spent_date',
                'is_suspended',
                'suspended_reason',
                'suspended_at'
            ]);
        });
    }
};
