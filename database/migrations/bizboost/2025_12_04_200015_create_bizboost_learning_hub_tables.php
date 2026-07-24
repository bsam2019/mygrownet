<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Courses
        Schema::create('bizboost_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('category'); // marketing, branding, sales, customer-service, social-media
            $table->string('difficulty')->default('beginner'); // beginner, intermediate, advanced
            $table->integer('duration_minutes')->default(0);
            $table->integer('lessons_count')->default(0);
            $table->string('tier_required')->default('free'); // free, basic, professional, business
            $table->boolean('has_certificate')->default(false);
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['category', 'is_published']);
            $table->index(['tier_required', 'is_published']);
        });

        // Lessons
        Schema::create('bizboost_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('bizboost_courses')->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('content')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            
            $table->unique(['course_id', 'slug']);
            $table->index(['course_id', 'sort_order']);
        });

        // Course Progress
        Schema::create('bizboost_course_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained('bizboost_courses')->onDelete('cascade');
            $table->foreignId('current_lesson_id')->nullable()->constrained('bizboost_lessons')->onDelete('set null');
            $table->json('completed_lessons')->nullable(); // Array of lesson IDs
            $table->integer('progress_percent')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'course_id']);
            $table->index(['user_id', 'completed_at']);
        });

        // Certificates
        Schema::create('bizboost_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained('bizboost_courses')->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->string('recipient_name');
            $table->timestamp('issued_at');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'course_id']);
            $table->index('certificate_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_certificates');
        Schema::dropIfExists('bizboost_course_progress');
        Schema::dropIfExists('bizboost_lessons');
        Schema::dropIfExists('bizboost_courses');
    }
};
