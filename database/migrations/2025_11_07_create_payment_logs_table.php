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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            
            // User reference
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Payment details
            $table->enum('payment_type', [
                'deposit',
                'withdrawal',
                'refund',
                'chargeback'
            ]);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('ZMW');
            
            // Payment provider details
            $table->enum('payment_method', [
                'mobile_money',
                'bank_transfer',
                'card',
                'cash',
                'other'
            ]);
            $table->string('provider')->nullable(); // MTN, Airtel, Visa, etc.
            $table->string('provider_reference')->nullable()->unique(); // External reference from provider
            $table->string('internal_reference')->unique(); // Our internal reference
            
            // Payment status tracking
            $table->enum('status', [
                'initiated',
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'refunded',
                'reconciled'
            ])->default('initiated');
            
            // Reconciliation
            $table->boolean('reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users');
            
            // Link to transaction (once payment is completed)
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            
            // Provider callback data (JSON for flexibility)
            $table->json('provider_data')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            // Timestamps
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index(['provider_reference']);
            $table->index(['internal_reference']);
            $table->index(['created_at']);
            $table->index(['reconciled', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
