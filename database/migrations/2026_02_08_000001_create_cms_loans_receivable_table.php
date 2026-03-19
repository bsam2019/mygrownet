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
        Schema::create('cms_loans_receivable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('loan_number')->unique(); // e.g., LOAN-2025-00001
            $table->string('loan_type')->default('member_loan'); // member_loan, business_loan, emergency_loan
            $table->decimal('principal_amount', 15, 2); // Original loan amount
            $table->decimal('interest_rate', 5, 2)->default(0); // Annual interest rate %
            $table->decimal('total_amount', 15, 2); // Principal + Interest
            $table->decimal('amount_paid', 15, 2)->default(0); // Total repaid so far
            $table->decimal('principal_paid', 15, 2)->default(0); // Principal portion paid
            $table->decimal('interest_paid', 15, 2)->default(0); // Interest portion paid
            $table->decimal('outstanding_balance', 15, 2); // Remaining balance
            $table->integer('term_months')->nullable(); // Loan term in months
            $table->decimal('monthly_payment', 15, 2)->nullable(); // Expected monthly payment
            $table->date('disbursement_date'); // When loan was given
            $table->date('due_date')->nullable(); // Final due date
            $table->date('next_payment_date')->nullable(); // Next expected payment
            $table->date('last_payment_date')->nullable(); // Last payment received
            $table->string('status')->default('active'); // active, paid, defaulted, written_off
            $table->integer('days_overdue')->default(0); // Days past due
            $table->string('risk_category')->default('current'); // current, 30_days, 60_days, 90_days, default
            $table->text('purpose')->nullable(); // Loan purpose/reason
            $table->text('notes')->nullable(); // Admin notes
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('disbursed_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('fully_paid_at')->nullable();
            $table->timestamp('defaulted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('risk_category');
            $table->index('disbursement_date');
            $table->index('due_date');
            $table->index(['company_id', 'status']);
            $table->index(['status', 'risk_category']);
        });

        Schema::create('cms_loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('cms_loans_receivable')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_reference')->unique(); // e.g., PAY-LOAN-2025-00001
            $table->decimal('payment_amount', 15, 2); // Total payment
            $table->decimal('principal_portion', 15, 2); // Amount applied to principal
            $table->decimal('interest_portion', 15, 2); // Amount applied to interest
            $table->decimal('penalty_portion', 15, 2)->default(0); // Late payment penalty
            $table->date('payment_date'); // When payment was made
            $table->string('payment_method')->nullable(); // wallet, bank_transfer, etc.
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('loan_id');
            $table->index('user_id');
            $table->index('payment_date');
        });

        Schema::create('cms_loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('cms_loans_receivable')->onDelete('cascade');
            $table->integer('installment_number'); // 1, 2, 3, etc.
            $table->date('due_date');
            $table->decimal('installment_amount', 15, 2); // Expected payment
            $table->decimal('principal_portion', 15, 2); // Principal in this installment
            $table->decimal('interest_portion', 15, 2); // Interest in this installment
            $table->decimal('amount_paid', 15, 2)->default(0); // Actual amount paid
            $table->string('status')->default('pending'); // pending, paid, overdue, partial
            $table->date('paid_date')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('loan_id');
            $table->index(['loan_id', 'due_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_loan_schedules');
        Schema::dropIfExists('cms_loan_repayments');
        Schema::dropIfExists('cms_loans_receivable');
    }
};
