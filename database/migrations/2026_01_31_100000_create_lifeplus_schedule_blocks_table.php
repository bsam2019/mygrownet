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
        Schema::create('lifeplus_schedule_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained('lifeplus_tasks')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('color')->default('#3b82f6'); // Blue
            $table->enum('category', ['work', 'personal', 'health', 'learning', 'social', 'other'])->default('other');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_pattern', ['daily', 'weekly', 'weekdays', 'weekends'])->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->boolean('is_synced')->default(true);
            $table->string('local_id')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'date', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lifeplus_schedule_blocks');
    }
};
