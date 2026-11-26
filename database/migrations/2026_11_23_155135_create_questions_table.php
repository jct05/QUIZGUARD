<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the questions table linked to question banks.
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->unsignedBigInteger('bank_id');
            $table->text('question_text');
            $table->string('question_type', 45);
            $table->text('options')->nullable();
            $table->string('correct_answers', 255)->nullable();
            $table->integer('points')->default(1);
            $table->string('status', 45)->default('active');
            $table->timestamps();

            $table->foreign('bank_id')->references('bank_id')->on('question_banks')->onDelete('cascade');
        });
    }

    // Drop the questions table when rolling back the migration.
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};