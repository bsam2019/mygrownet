<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('detailed_description')->nullable();
            $table->enum('type', ['real_estate', 'agriculture', 'sme', 'digital', 'infrastructure'])->default('sme');
            $table->enum('category', ['property_development', 'farming', 'business_venture', 'technology', 'community_infrastructure']);
            
            // Financial details
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->decimal('minimum_contribution', 10, 2)->default(100);
            $table->decimal('maximum_contribution', 12, 2)->nullable();
            $table->decimal('expected_annual_return', 5, 2)->default(0); // Percentage
            $table->integer('project_duration_months')->default(12);
            
            // Project timeline
            $table->date('funding_start_date');
            $table->date('funding_end_date');
            $table->date('project_start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            
            // Status and governance
            $table->enum('status', ['planning', 'funding', 'active', 'completed', 'cancelled', 'paused'])->default('planning');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('requires_voting')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('auto_approve_contributions')->default(false);
            
            // Tier access control
            $table->json('required_membership_tiers')->nullable(); // Which tiers can participate
            $table->json('tier_contribution_limits')->nullable(); // Different limits per tier
            $table->json('tier_voting_weights')->nullable(); // Different voting power per tier
            
            // Project management
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->json('project_milestones')->nullable();
            $table->json('risk_factors')->nullable();
            $table->json('success_metrics')->nullable();
            
            // Media and documentation
            $table->string('featured_image_url')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('documents')->nullable(); // Business plans, legal docs, etc.
            
            // Community engagement
            $table->integer('total_contributors')->default(0);
            $table->integer('total_votes')->default(0);
            $table->decimal('community_approval_rating', 3, 2)->default(0); // 0-100%
            
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['status', 'type']);
            $table->index(['funding_start_date', 'funding_end_date']);
            $table->index(['target_amount', 'current_amount']);
            $table->index(['created_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_projects');
    }
};