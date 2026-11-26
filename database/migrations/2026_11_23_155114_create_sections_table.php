<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the sections table linked to teachers and subjects.
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id('section_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('section_name', 45);
            $table->boolean('is_locked')->default(false);
            $table->string('status', 45)->default('active');
            $table->timestamps();

            $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('subject_id')->on('subjects')->onDelete('cascade');
        });
    }

    // Drop the sections table when rolling back the migration.
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};