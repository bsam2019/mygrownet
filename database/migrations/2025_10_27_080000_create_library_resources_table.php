<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['pdf', 'video', 'article', 'course', 'tool', 'template'])->default('article');
            $table->enum('category', ['business', 'marketing', 'finance', 'leadership', 'personal_development', 'network_building'])->default('business');
            $table->string('resource_url')->nullable(); // External URL or file path
            $table->string('thumbnail')->nullable();
            $table->string('author')->nullable();
            $table->integer('duration_minutes')->nullable(); // For videos/courses
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->boolean('is_external')->default(false); // External link vs uploaded file
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });

        // Track member access to library resources
        Schema::create('library_resource_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('library_resource_id')->constrained()->onDelete('cascade');
            $table->timestamp('accessed_at');
            $table->integer('time_spent_seconds')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'library_resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_resource_access');
        Schema::dropIfExists('library_resources');
    }
};
