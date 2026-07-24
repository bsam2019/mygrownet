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
        Schema::create('hiring_roadmap', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');
            $table->enum('phase', ['phase_1', 'phase_2', 'phase_3'])->default('phase_1');
            $table->date('target_hire_date')->nullable();
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->integer('headcount')->default(1);
            $table->enum('status', ['planned', 'in_progress', 'hired', 'cancelled'])->default('planned');
            $table->decimal('budget_allocated', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->index(['phase', 'status']);
            $table->index('target_hire_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hiring_roadmap');
    }
};
