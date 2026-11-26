<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the users table with all required user columns.
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('role', 45);
            $table->integer('school_id_num')->unique();
            $table->string('first_name', 45);
            $table->string('middle_name', 45)->nullable();
            $table->string('last_name', 45);
            $table->string('dwcc_email', 45)->unique();
            $table->string('password_hash', 255);
            $table->string('department', 45)->nullable();
            $table->string('status', 45)->default('active');
            $table->string('otp_hash', 255)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->integer('otp_attempts')->default(0);
            $table->timestamps();
        });
    }

    // Drop the users table when rolling back the migration.
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};