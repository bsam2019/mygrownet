<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('category')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('growfinance_accounts')->nullOnDelete();
            
            // Budget period
            $table->enum('period', ['monthly', 'quarterly', 'yearly', 'custom']);
            $table->date('start_date');
            $table->date('end_date');
            
            // Amounts
            $table->decimal('budgeted_amount', 15, 2);
            $table->decimal('spent_amount', 15, 2)->default(0);
            
            // Settings
            $table->boolean('is_active')->default(true);
            $table->boolean('rollover_unused')->default(false);
            $table->integer('alert_threshold')->default(80); // Alert at 80% spent
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['business_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_budgets');
    }
};
