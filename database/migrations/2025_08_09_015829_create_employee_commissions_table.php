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
        Schema::create('employee_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->enum('commission_type', ['base', 'performance', 'bonus', 'referral']);
            $table->string('source_reference')->nullable(); // Reference to source transaction/client
            $table->decimal('amount', 10, 2);
            $table->decimal('rate_applied', 5, 4)->nullable(); // Commission rate used
            $table->date('earned_date');
            $table->date('payment_date')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->json('calculation_details')->nullable(); // Store calculation breakdown
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('employee_id', 'idx_commission_employee');
            $table->index('commission_type', 'idx_commission_type');
            $table->index('payment_status', 'idx_commission_payment_status');
            $table->index('earned_date', 'idx_commission_earned_date');
            $table->index('payment_date', 'idx_commission_payment_date');
            $table->index(['employee_id', 'earned_date'], 'idx_commission_employee_date');
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_commissions');
    }
};
