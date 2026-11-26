<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the activity logs table for tracking user actions.
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->integer('school_id_num')->nullable();
            $table->string('role', 45)->nullable();
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    // Drop the activity logs table when rolling back migration.
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};