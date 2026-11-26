<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the question banks table linked to teachers and subjects.
    public function up(): void
    {
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id('bank_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('bank_title', 100);
            $table->string('source_file_name', 255)->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->string('status', 45)->default('active');
            $table->timestamps();

            $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('subject_id')->on('subjects')->onDelete('cascade');
        });
    }

    // Drop the question banks table when rolling back migration.
    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};