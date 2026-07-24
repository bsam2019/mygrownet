<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_completions')) {
            Schema::create('course_completions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('course_id');
                $table->timestamp('completed_at');
                $table->integer('completion_time_minutes')->nullable();
                $table->decimal('final_score', 5, 2)->nullable();
                $table->boolean('certificate_eligible')->default(false);
                $table->string('tier_at_completion')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'course_id']);
                $table->index(['user_id', 'completed_at']);
                $table->index(['course_id', 'completed_at']);
                $table->index(['tier_at_completion', 'completed_at']);
            });
        }

        // Add foreign keys only if referenced tables exist
        try {
            if (Schema::hasTable('course_completions') && Schema::hasTable('users')) {
                Schema::table('course_completions', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}

        try {
            if (Schema::hasTable('course_completions') && Schema::hasTable('courses')) {
                Schema::table('course_completions', function (Blueprint $table) {
                    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        Schema::dropIfExists('course_completions');
    }
};