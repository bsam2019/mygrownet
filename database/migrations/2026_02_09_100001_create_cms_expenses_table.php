<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('expense_number', 50);
            $table->foreignId('category_id')->constrained('cms_expense_categories');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'mtn_momo', 'airtel_money', 'company_card']);
            $table->string('receipt_number', 100)->nullable();
            $table->string('receipt_path', 255)->nullable();
            $table->date('expense_date');
            $table->boolean('requires_approval')->default(false);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('cms_users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('recorded_by')->constrained('cms_users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'expense_number'], 'unique_expense_number');
            $table->index(['company_id', 'expense_date']);
            $table->index('category_id');
            $table->index('job_id');
            $table->index(['company_id', 'approval_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_expenses');
    }
};
