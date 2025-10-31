<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bgf_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('bgf_applications')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('project_number')->unique();
            
            // Contract Details
            $table->decimal('approved_amount', 12, 2);
            $table->decimal('member_contribution', 12, 2);
            $table->integer('member_profit_percentage'); // 60-70%
            $table->integer('mygrownet_profit_percentage'); // 30-40%
            $table->date('expected_completion_date');
            
            // Contract Document
            $table->text('contract_url')->nullable();
            $table->timestamp('contract_signed_at')->nullable();
            
            // Project Status
            $table->enum('status', [
                'pending_contract',
                'active',
                'in_progress',
                'completed',
                'defaulted',
                'cancelled'
            ])->default('pending_contract');
            
            // Financial Tracking
            $table->decimal('total_disbursed', 12, 2)->default(0);
            $table->decimal('total_repaid', 12, 2)->default(0);
            $table->decimal('actual_profit', 12, 2)->nullable();
            $table->decimal('member_profit_share', 12, 2)->nullable();
            $table->decimal('mygrownet_profit_share', 12, 2)->nullable();
            
            // Performance
            $table->date('actual_completion_date')->nullable();
            $table->boolean('completed_on_time')->nullable();
            $table->integer('performance_rating')->nullable(); // 1-5 stars
            $table->text('completion_notes')->nullable();
            
            // Milestones
            $table->json('milestones')->nullable();
            $table->integer('milestones_completed')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
            $table->index('project_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bgf_projects');
    }
};
