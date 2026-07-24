<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bgf_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('bgf_projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('repayment_number')->unique();
            
            // Repayment Details
            $table->decimal('total_revenue', 12, 2);
            $table->decimal('total_costs', 12, 2);
            $table->decimal('net_profit', 12, 2);
            $table->decimal('member_share', 12, 2);
            $table->decimal('mygrownet_share', 12, 2);
            
            // Payment Details
            $table->enum('payment_method', ['bank_transfer', 'mobile_money', 'wallet', 'cash']);
            $table->string('transaction_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Status
            $table->enum('status', [
                'pending_report',
                'report_submitted',
                'under_review',
                'approved',
                'paid',
                'disputed'
            ])->default('pending_report');
            
            // Profit Report
            $table->json('revenue_breakdown')->nullable();
            $table->json('cost_breakdown')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->text('member_notes')->nullable();
            
            // Verification
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->boolean('verified')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'status']);
            $table->index('repayment_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bgf_repayments');
    }
};
