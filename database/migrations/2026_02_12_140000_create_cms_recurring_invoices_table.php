<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('cms_customers')->onDelete('cascade');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->onDelete('set null');
            
            // Template details
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('items'); // Invoice line items
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            
            // Recurrence settings
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->integer('interval')->default(1); // Every X days/weeks/months/years
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('max_occurrences')->nullable();
            $table->integer('occurrences_count')->default(0);
            
            // Next generation
            $table->date('next_generation_date');
            
            // Status
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            
            // Email settings
            $table->boolean('auto_send_email')->default(true);
            $table->string('email_to')->nullable();
            $table->string('email_cc')->nullable();
            
            // Payment terms
            $table->integer('payment_terms_days')->default(30);
            $table->text('notes')->nullable();
            
            // Metadata
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'next_generation_date']);
        });

        // Track generated invoices
        Schema::create('cms_recurring_invoice_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_invoice_id')->constrained('cms_recurring_invoices')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('cms_invoices')->onDelete('cascade');
            $table->date('generated_date');
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
            
            // Use custom shorter index name to avoid MySQL 64-char limit
            $table->index(['recurring_invoice_id', 'generated_date'], 'cms_rec_inv_hist_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_recurring_invoice_history');
        Schema::dropIfExists('cms_recurring_invoices');
    }
};
