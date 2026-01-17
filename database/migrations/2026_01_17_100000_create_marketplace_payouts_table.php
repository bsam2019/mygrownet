<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketplace_payouts')) {
            return;
        }
        
        Schema::create('marketplace_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->integer('amount'); // Amount in ngwee (K1 = 100 ngwee)
            $table->integer('commission_deducted')->default(0); // Commission amount deducted
            $table->integer('net_amount'); // Amount after commission
            $table->string('payout_method'); // momo, airtel, bank
            $table->string('account_number');
            $table->string('account_name');
            $table->string('bank_name')->nullable(); // For bank transfers
            $table->string('status')->default('pending'); // pending, approved, processing, completed, rejected, failed
            $table->string('reference')->unique(); // Unique payout reference
            $table->text('seller_notes')->nullable(); // Notes from seller
            $table->text('admin_notes')->nullable(); // Notes from admin
            $table->text('rejection_reason')->nullable(); // Reason if rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->string('transaction_reference')->nullable(); // External payment reference
            $table->json('metadata')->nullable(); // Additional data (API responses, etc.)
            $table->timestamps();
            
            // Indexes
            $table->index('seller_id');
            $table->index('status');
            $table->index('reference');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_payouts');
    }
};
