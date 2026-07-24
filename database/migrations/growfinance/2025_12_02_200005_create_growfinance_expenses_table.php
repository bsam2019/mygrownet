<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('growfinance_vendors')->nullOnDelete();
            $table->foreignId('account_id')->constrained('growfinance_accounts')->cascadeOnDelete();
            $table->date('expense_date');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'bank', 'mobile_money', 'credit'])->default('cash');
            $table->string('reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'expense_date']);
            $table->index(['business_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_expenses');
    }
};
