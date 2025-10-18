<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('learning_objectives')->nullable();
            $table->enum('category', ['financial_literacy', 'business_skills', 'life_skills', 'investment_strategies']);
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced']);
            $table->integer('estimated_duration_minutes')->default(0);
            $table->string('thumbnail_url')->nullable();
            $table->json('required_subscription_packages'); // Which packages can access this course
            $table->json('required_membership_tiers')->nullable(); // Which tiers can access this course
            $table->boolean('is_premium')->default(false);
            $table->boolean('certificate_eligible')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('published_at')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index(['difficulty_level', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};