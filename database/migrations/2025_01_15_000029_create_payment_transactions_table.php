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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // commission_payment, subscription_payment, bonus_payment, withdrawal, refund
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, processing, completed, failed, cancelled
            $table->string('payment_method')->default('mobile_money'); // mobile_money, bank_transfer, cash, wallet
            $table->json('payment_details')->nullable(); // phone_number, commission_ids, etc.
            $table->string('reference')->unique(); // Internal reference
            $table->string('external_reference')->nullable(); // External provider reference
            $table->json('payment_response')->nullable(); // Provider response data
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'type']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('reference');
            $table->index('external_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};