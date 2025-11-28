<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dividend Declarations (company-wide)
        if (!Schema::hasTable('dividend_declarations')) {
        Schema::create('dividend_declarations', function (Blueprint $table) {
            $table->id();
            $table->string('declaration_name'); // e.g., "Q4 2024 Dividend"
            $table->decimal('total_amount', 15, 2);
            $table->decimal('per_share_amount', 10, 4)->nullable();
            $table->date('declaration_date');
            $table->date('record_date'); // Who's eligible
            $table->date('payment_date');
            $table->enum('status', ['declared', 'approved', 'paid', 'cancelled'])->default('declared');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('payment_date');
            $table->index('status');
        });
        }

        // Individual Investor Dividends
        if (!Schema::hasTable('investor_dividends')) {
        Schema::create('investor_dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
            $table->foreignId('dividend_declaration_id')->nullable()->constrained('dividend_declarations')->onDelete('set null');
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('tax_withheld', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->date('payment_date');
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable(); // bank_transfer, mobile_money, etc.
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['investor_account_id', 'payment_date']);
            $table->index('status');
        });
        }

        // Payment Methods
        if (!Schema::hasTable('investor_payment_methods')) {
        Schema::create('investor_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
            $table->enum('method_type', ['bank_transfer', 'mobile_money', 'check']);
            $table->boolean('is_primary')->default(false);
            
            // Bank details
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('swift_code')->nullable();
            
            // Mobile money details
            $table->string('mobile_provider')->nullable(); // MTN, Airtel, etc.
            $table->string('mobile_number')->nullable();
            
            // Check details
            $table->text('mailing_address')->nullable();
            
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->index('investor_account_id');
        });
        }

        // Tax Documents
        if (!Schema::hasTable('investor_tax_documents')) {
        Schema::create('investor_tax_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
            $table->integer('tax_year');
            $table->enum('document_type', ['dividend_statement', 'tax_certificate', 'annual_summary']);
            $table->decimal('total_dividends', 15, 2)->default(0);
            $table->decimal('total_tax_withheld', 15, 2)->default(0);
            $table->string('file_path')->nullable();
            $table->date('generated_at');
            $table->timestamps();
            
            $table->index(['investor_account_id', 'tax_year']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_tax_documents');
        Schema::dropIfExists('investor_payment_methods');
        Schema::dropIfExists('investor_dividends');
        Schema::dropIfExists('dividend_declarations');
    }
};
