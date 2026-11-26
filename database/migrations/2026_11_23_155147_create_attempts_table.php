<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the attempts table linked to quizzes and students.
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id('attempt_id');
            $table->unsignedBigInteger('quiz_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('score')->default(0);
            $table->integer('total_points')->default(0);
            $table->string('status', 45)->default('in_progress');
            $table->integer('violation_count')->default(0);
            $table->timestamps();

            $table->foreign('quiz_id')->references('quiz_id')->on('quizzes')->onDelete('cascade');
            $table->foreign('student_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    // Drop the attempts table when rolling back the migration.
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};