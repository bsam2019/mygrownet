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
        Schema::create('commission_clawbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_commission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User whose commission is being clawed back
            $table->foreignId('withdrawal_request_id')->nullable()->constrained()->nullOnDelete(); // Related withdrawal that triggered clawback
            $table->decimal('original_amount', 15, 2); // Original commission amount
            $table->decimal('clawback_amount', 15, 2); // Amount being clawed back
            $table->decimal('clawback_percentage', 5, 2); // Percentage of clawback (25% or 50%)
            $table->enum('reason', ['early_withdrawal', 'policy_violation', 'manual_adjustment'])->default('early_withdrawal');
            $table->enum('status', ['pending', 'processed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['user_id', 'status']);
            $table->index(['referral_commission_id', 'status']);
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_clawbacks');
    }
};
