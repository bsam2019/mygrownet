<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('community_project_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refunded'])->default('pending');
            $table->string('tier_at_contribution')->nullable();
            $table->timestamp('contributed_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Transaction tracking
            $table->string('transaction_reference')->nullable();
            $table->enum('payment_method', ['bank_transfer', 'mobile_money', 'cash', 'internal_balance'])->nullable();
            $table->json('payment_details')->nullable();
            
            // Profit sharing tracking
            $table->decimal('total_returns_received', 12, 2)->default(0);
            $table->decimal('expected_annual_return', 5, 2)->nullable(); // Locked-in rate at contribution time
            $table->boolean('auto_reinvest')->default(false);
            
            // Administrative
            $table->text('notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('cancellation_reason')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['community_project_id', 'status']);
            $table->index(['contributed_at', 'status']);
            $table->index(['tier_at_contribution', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_contributions');
    }
};