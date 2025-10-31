<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bgf_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('bgf_projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('disbursement_number')->unique();
            
            // Disbursement Details
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['initial', 'milestone', 'final', 'direct_vendor']);
            $table->text('purpose');
            $table->string('milestone_reference')->nullable();
            
            // Payment Details
            $table->enum('payment_method', ['bank_transfer', 'mobile_money', 'direct_vendor', 'cash']);
            $table->string('recipient_name');
            $table->string('recipient_account')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('vendor_details')->nullable();
            
            // Status
            $table->enum('status', [
                'pending',
                'approved',
                'processing',
                'completed',
                'failed',
                'cancelled'
            ])->default('pending');
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Transaction
            $table->string('transaction_reference')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->text('disbursement_notes')->nullable();
            
            // Supporting Documents
            $table->json('documents')->nullable(); // receipts, invoices
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'status']);
            $table->index('disbursement_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bgf_disbursements');
    }
};
