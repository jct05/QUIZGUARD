<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the quizzes table with all quiz configuration columns.
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id('quiz_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('bank_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('section_id');
            $table->string('quiz_code', 12)->unique();
            $table->string('title', 100);
            $table->integer('time_limit_min')->nullable();
            $table->integer('time_per_question_sec')->nullable();
            $table->integer('max_violation')->default(3);
            $table->integer('passing_score')->nullable();
            $table->integer('attempts_allowed')->default(1);
            $table->boolean('shuffle_questions')->default(false);
            $table->integer('number_of_questions')->nullable();
            $table->boolean('shuffle_answers')->default(false);
            $table->boolean('allow_review')->default(false);
            $table->boolean('disable_backtracking')->default(false);
            $table->timestamp('open_at')->nullable();
            $table->string('status', 45)->default('draft');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('bank_id')->references('bank_id')->on('question_banks')->onDelete('cascade');
            $table->foreign('subject_id')->references('subject_id')->on('subjects')->onDelete('cascade');
            $table->foreign('section_id')->references('section_id')->on('sections')->onDelete('cascade');
        });
    }

    // Drop the quizzes table when rolling back the migration.
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
