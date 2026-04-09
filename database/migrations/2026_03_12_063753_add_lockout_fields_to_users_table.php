<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// tambahin lockout ke tabel user

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('locked_until')->nullable()->after('password');
            $table->integer('failed_login_attempts')->default(0)->after('locked_until');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locked_until', 'failed_login_attempts']);
        });
    }
};