<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_restarted')->default(false);
            $table->unsignedBigInteger('current_chapter_index')->default(0);
            $table->unsignedBigInteger('current_lesson_index')->default(0);
            $table->unsignedBigInteger('pending_exam_chapter_id')->nullable();
            $table->decimal('chapter_exam_score', 5, 2)->nullable();
            $table->decimal('final_exam_score', 5, 2)->nullable();
            $table->timestamp('last_failed_final_exam_at')->nullable();
            $table->index(['pending_exam_chapter_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_user');
    }
};