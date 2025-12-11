<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstart_user_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_journey_id')->constrained('growstart_user_journeys')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('growstart_tasks')->cascadeOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->unique(['user_journey_id', 'task_id']);
            $table->index(['user_journey_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstart_user_tasks');
    }
};
