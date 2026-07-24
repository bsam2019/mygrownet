<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_lessons')) {
            Schema::create('course_lessons', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id');
                $table->string('title');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->longText('content')->nullable();
                $table->enum('content_type', ['text', 'video', 'document', 'interactive', 'quiz'])->default('text');
                $table->string('video_url')->nullable();
                $table->string('document_url')->nullable();
                $table->integer('duration_minutes')->default(0);
                $table->integer('order')->default(0);
                $table->boolean('is_preview')->default(false);
                $table->enum('required_tier_level', ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'])->nullable();
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->timestamps();

                $table->index(['course_id', 'order']);
                $table->index(['course_id', 'status']);
                $table->index(['required_tier_level', 'status']);
            });
        }

        // Add FK only when courses table exists
        try {
            if (Schema::hasTable('course_lessons') && Schema::hasTable('courses')) {
                Schema::table('course_lessons', function (Blueprint $table) {
                    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lessons');
    }
};