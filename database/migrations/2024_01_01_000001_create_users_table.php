<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            
            // MASALAH: Tidak ada field untuk lockout
            // Seharusnya ada:
            // $table->timestamp('locked_until')->nullable();
            // $table->integer('failed_login_attempts')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
