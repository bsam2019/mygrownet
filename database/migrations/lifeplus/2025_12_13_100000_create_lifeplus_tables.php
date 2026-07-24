<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User Profiles for Life+
        Schema::create('lifeplus_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('location')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        // Expense Categories
        Schema::create('lifeplus_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->default('💰');
            $table->string('color')->default('#3b82f6');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Expenses
        Schema::create('lifeplus_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('lifeplus_expense_categories')->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->string('description')->nullable();
            $table->date('expense_date');
            $table->boolean('is_synced')->default(true);
            $table->string('local_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'expense_date']);
        });

        // Budgets
        Schema::create('lifeplus_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('lifeplus_expense_categories')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('period', ['weekly', 'monthly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'period']);
        });

        // Savings Goals
        Schema::create('lifeplus_savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('target_amount', 12, 2);
            $table->decimal('current_amount', 12, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();
        });

        // Tasks
        Schema::create('lifeplus_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_synced')->default(true);
            $table->string('local_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'is_completed']);
        });

        // Habits
        Schema::create('lifeplus_habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->default('⭐');
            $table->string('color')->default('#10b981');
            $table->enum('frequency', ['daily', 'weekly'])->default('daily');
            $table->time('reminder_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Habit Logs
        Schema::create('lifeplus_habit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained('lifeplus_habits')->onDelete('cascade');
            $table->date('completed_date');
            $table->timestamps();
            $table->unique(['habit_id', 'completed_date']);
        });

        // Notes
        Schema::create('lifeplus_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_synced')->default(true);
            $table->string('local_id')->nullable();
            $table->timestamps();
        });

        // Gigs
        Schema::create('lifeplus_gigs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('payment_amount', 12, 2)->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['open', 'assigned', 'completed', 'cancelled'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index(['status', 'location']);
        });

        // Gig Applications
        Schema::create('lifeplus_gig_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gig_id')->constrained('lifeplus_gigs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
            $table->unique(['gig_id', 'user_id']);
        });

        // Community Posts
        Schema::create('lifeplus_community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['notice', 'event', 'lost_found'])->default('notice');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('location')->nullable();
            $table->datetime('event_date')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_promoted')->default(false);
            $table->datetime('expires_at')->nullable();
            $table->timestamps();
            $table->index(['type', 'created_at']);
        });

        // Knowledge Items
        Schema::create('lifeplus_knowledge_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('category')->nullable();
            $table->enum('type', ['article', 'audio', 'video'])->default('article');
            $table->string('media_url')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_daily_tip')->default(false);
            $table->timestamps();
        });

        // User Downloads
        Schema::create('lifeplus_user_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('knowledge_item_id')->constrained('lifeplus_knowledge_items')->onDelete('cascade');
            $table->timestamp('downloaded_at');
            $table->unique(['user_id', 'knowledge_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lifeplus_user_downloads');
        Schema::dropIfExists('lifeplus_knowledge_items');
        Schema::dropIfExists('lifeplus_community_posts');
        Schema::dropIfExists('lifeplus_gig_applications');
        Schema::dropIfExists('lifeplus_gigs');
        Schema::dropIfExists('lifeplus_notes');
        Schema::dropIfExists('lifeplus_habit_logs');
        Schema::dropIfExists('lifeplus_habits');
        Schema::dropIfExists('lifeplus_tasks');
        Schema::dropIfExists('lifeplus_savings_goals');
        Schema::dropIfExists('lifeplus_budgets');
        Schema::dropIfExists('lifeplus_expenses');
        Schema::dropIfExists('lifeplus_expense_categories');
        Schema::dropIfExists('lifeplus_user_profiles');
    }
};
