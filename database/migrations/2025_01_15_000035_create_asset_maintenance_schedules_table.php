<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_id')->constrained('physical_reward_allocations')->onDelete('cascade');
            $table->enum('milestone_type', [
                'performance_review',
                'mid_term_assessment', 
                'pre_transfer_evaluation',
                'ownership_transfer'
            ]);
            $table->date('due_date');
            $table->enum('status', ['pending', 'completed', 'skipped'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('performance_data')->nullable(); // Store performance metrics at time of review
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['due_date', 'status']);
            $table->index(['allocation_id', 'milestone_type']);
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_schedules');
    }
};