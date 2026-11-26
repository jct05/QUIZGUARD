<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the section students pivot table for student enrollments.
    public function up(): void
    {
        Schema::create('section_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('student_id');
            $table->timestamps();

            $table->foreign('section_id')->references('section_id')->on('sections')->onDelete('cascade');
            $table->foreign('student_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    // Drop the section students table when rolling back migration.
    public function down(): void
    {
        Schema::dropIfExists('section_students');
    }
};