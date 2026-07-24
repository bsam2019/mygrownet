<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bgf_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('bgf_applications')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            
            // Scoring Breakdown (Total 100%)
            $table->integer('membership_score')->default(0); // 15%
            $table->integer('training_score')->default(0); // 10%
            $table->integer('viability_score')->default(0); // 25%
            $table->integer('credibility_score')->default(0); // 15%
            $table->integer('contribution_score')->default(0); // 15%
            $table->integer('risk_control_score')->default(0); // 10%
            $table->integer('track_record_score')->default(0); // 10%
            
            // Total Score
            $table->integer('total_score')->default(0); // 0-100
            $table->enum('recommendation', ['approve', 'reject', 'request_more_info'])->nullable();
            
            // Detailed Notes
            $table->text('membership_notes')->nullable();
            $table->text('training_notes')->nullable();
            $table->text('viability_notes')->nullable();
            $table->text('credibility_notes')->nullable();
            $table->text('contribution_notes')->nullable();
            $table->text('risk_control_notes')->nullable();
            $table->text('track_record_notes')->nullable();
            $table->text('overall_notes')->nullable();
            
            // Risk Assessment
            $table->enum('risk_level', ['low', 'medium', 'high'])->nullable();
            $table->json('risk_factors')->nullable();
            $table->json('mitigation_suggestions')->nullable();
            
            $table->timestamps();
            
            $table->index('application_id');
            $table->index('evaluator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bgf_evaluations');
    }
};
