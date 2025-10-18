<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_enrollments')) {
            Schema::create('course_enrollments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('course_id');
                $table->timestamp('enrolled_at')->default(now());
                $table->string('tier_at_enrollment')->nullable(); // Track tier when enrolled
                $table->decimal('progress_percentage', 5, 2)->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('certificate_issued_at')->nullable();
                $table->enum('status', ['enrolled', 'in_progress', 'completed', 'dropped'])->default('enrolled');
                $table->timestamps();

                $table->unique(['user_id', 'course_id']);
                $table->index(['user_id', 'status']);
                $table->index(['course_id', 'status']);
            });
        }

        // Add foreign keys only if referenced tables exist
        try {
            if (Schema::hasTable('course_enrollments') && Schema::hasTable('users')) {
                Schema::table('course_enrollments', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}

        try {
            if (Schema::hasTable('course_enrollments') && Schema::hasTable('courses')) {
                Schema::table('course_enrollments', function (Blueprint $table) {
                    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};