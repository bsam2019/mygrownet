<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->string('category', 100)->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern', 50)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('personal_todos')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'priority']);
            $table->index(['user_id', 'category']);
        });

        Schema::create('todo_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_id')->constrained('personal_todos')->onDelete('cascade');
            $table->timestamp('remind_at');
            $table->enum('channel', ['push', 'email', 'sms'])->default('push');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['remind_at', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todo_reminders');
        Schema::dropIfExists('personal_todos');
    }
};
