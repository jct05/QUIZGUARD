<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Create the subjects table linked to teacher users.
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id('subject_id');
            $table->unsignedBigInteger('teacher_id');
            $table->string('subject_code', 45);
            $table->string('subject_name', 100);
            $table->string('status', 45)->default('active');
            $table->timestamps();

            $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    // Drop the subjects table when rolling back the migration.
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};