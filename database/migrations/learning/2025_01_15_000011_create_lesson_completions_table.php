<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_lesson_id')->constrained()->onDelete('cascade');
            $table->timestamp('completed_at');
            $table->integer('time_spent_minutes')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(100);
            $table->timestamps();

            $table->unique(['user_id', 'course_lesson_id']);
            $table->index(['user_id', 'completed_at']);
            $table->index(['course_lesson_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_completions');
    }
};