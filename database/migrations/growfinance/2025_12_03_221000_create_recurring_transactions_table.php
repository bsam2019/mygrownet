<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['income', 'expense']);
            
            // For expenses
            $table->foreignId('account_id')->nullable()->constrained('growfinance_accounts')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('growfinance_vendors')->nullOnDelete();
            
            // For income/invoices
            $table->foreignId('customer_id')->nullable()->constrained('growfinance_customers')->nullOnDelete();
            
            $table->string('description');
            $table->string('category')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            
            // Recurrence settings
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_due_date');
            $table->date('last_processed_date')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('occurrences_count')->default(0);
            $table->integer('max_occurrences')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['business_id', 'is_active']);
            $table->index(['next_due_date', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_recurring_transactions');
    }
};
