<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the violation logs table linked to attempts and questions.
    public function up(): void
    {
        Schema::create('violation_logs', function (Blueprint $table) {
            $table->id('violation_id');
            $table->unsignedBigInteger('attempt_id');
            $table->string('violation_type', 45);
            $table->timestamp('occurred_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->unsignedBigInteger('question_id')->nullable();
            $table->timestamps();

            $table->foreign('attempt_id')->references('attempt_id')->on('attempts')->onDelete('cascade');
            $table->foreign('question_id')->references('question_id')->on('questions')->onDelete('set null');
        });
    }

    // Drop the violation logs table when rolling back migration.
    public function down(): void
    {
        Schema::dropIfExists('violation_logs');
    }
};