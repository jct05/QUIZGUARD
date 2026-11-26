<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the student answers table linked to attempts and questions.
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id('answer_id');
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->text('student_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->foreign('attempt_id')->references('attempt_id')->on('attempts')->onDelete('cascade');
            $table->foreign('question_id')->references('question_id')->on('questions')->onDelete('cascade');
        });
    }

    // Drop the student answers table when rolling back migration.
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};