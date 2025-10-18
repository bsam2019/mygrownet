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
        Schema::create('employee_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->enum('evaluation_period', ['monthly', 'quarterly', 'annual']);
            $table->date('period_start');
            $table->date('period_end');
            $table->json('metrics'); // Store performance metrics as JSON
            $table->decimal('overall_score', 5, 2); // Score out of 100
            $table->enum('rating', ['excellent', 'good', 'satisfactory', 'needs_improvement', 'unsatisfactory']);
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals_next_period')->nullable();
            $table->text('reviewer_comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('employee_id', 'idx_performance_employee');
            $table->index('reviewer_id', 'idx_performance_reviewer');
            $table->index(['employee_id', 'evaluation_period', 'period_start'], 'idx_performance_period');
            $table->index('status', 'idx_performance_status');
            $table->index('overall_score', 'idx_performance_score');
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('employees')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_performance');
    }
};
