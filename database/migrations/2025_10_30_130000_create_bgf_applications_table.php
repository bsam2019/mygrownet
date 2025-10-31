<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bgf_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->unique();
            
            // Business Details
            $table->string('business_name');
            $table->text('business_description');
            $table->string('business_type'); // agriculture, manufacturing, trade, services, education
            $table->string('order_type'); // order_fulfillment, venture, expansion
            
            // Financial Details
            $table->decimal('amount_requested', 12, 2);
            $table->decimal('member_contribution', 12, 2);
            $table->decimal('total_project_cost', 12, 2);
            $table->decimal('expected_profit', 12, 2);
            $table->integer('completion_period_days'); // 30-120 days
            
            // Supporting Documents
            $table->json('documents')->nullable(); // LPO, quotations, invoices
            $table->text('order_proof')->nullable(); // Description or link
            $table->text('feasibility_summary');
            
            // Client/Customer Details
            $table->string('client_name')->nullable();
            $table->string('client_contact')->nullable();
            $table->text('client_verification')->nullable();
            
            // Member Information
            $table->string('tpin')->nullable();
            $table->string('business_account')->nullable();
            $table->boolean('has_business_experience')->default(false);
            $table->text('previous_projects')->nullable();
            
            // Evaluation
            $table->integer('score')->nullable(); // 0-100
            $table->json('score_breakdown')->nullable();
            $table->text('evaluator_notes')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users');
            $table->timestamp('evaluated_at')->nullable();
            
            // Status
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'withdrawn'
            ])->default('draft');
            
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'status']);
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bgf_applications');
    }
};
