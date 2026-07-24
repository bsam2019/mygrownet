<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained('ventures')->onDelete('restrict');
            $table->foreignId('shareholder_id')->constrained('venture_shareholders')->onDelete('restrict');
            
            // Dividend Details
            $table->string('dividend_period'); // e.g., "Q1 2025", "2025 Annual"
            $table->date('declaration_date');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('equity_percentage_at_payment', 5, 4);
            
            // Payment
            $table->enum('payment_method', ['wallet', 'mobile_money', 'bank_transfer'])->default('wallet');
            $table->string('payment_reference')->nullable();
            $table->enum('status', ['declared', 'processing', 'paid', 'failed'])->default('declared');
            $table->timestamp('paid_at')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            $table->index(['venture_id', 'dividend_period']);
            $table->index(['shareholder_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_dividends');
    }
};
