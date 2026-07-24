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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tier_id')->constrained('investment_tiers')->onDelete('cascade');
            $table->string('status')->default('active'); // active, suspended, cancelled, expired
            $table->timestamp('started_at');
            $table->timestamp('next_billing_date');
            $table->timestamp('last_payment_at')->nullable();
            $table->decimal('last_payment_amount', 10, 2)->nullable();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->onDelete('set null');
            $table->integer('failed_payment_attempts')->default(0);
            
            // Suspension fields
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable(); // failed_payments, user_request, admin_action, policy_violation
            
            // Downgrade tracking
            $table->timestamp('downgraded_at')->nullable();
            $table->string('downgrade_reason')->nullable(); // failed_payments, user_request
            
            // Upgrade tracking
            $table->timestamp('upgraded_at')->nullable();
            $table->string('upgrade_reason')->nullable(); // user_requested, tier_qualification
            
            // Cancellation fields
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable(); // user_request, failed_payments, admin_action, policy_violation
            
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['status', 'next_billing_date']);
            $table->index(['tier_id', 'status']);
            $table->index('next_billing_date');
            $table->index('failed_payment_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};