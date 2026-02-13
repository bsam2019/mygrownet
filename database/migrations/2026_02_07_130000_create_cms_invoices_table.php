<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('cms_customers')->onDelete('restrict');
            $table->foreignId('job_id')->nullable()->constrained('cms_jobs')->onDelete('restrict');
            
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2);
            
            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->index('invoice_date');
            $table->index('due_date');
        });

        Schema::create('cms_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('cms_invoices')->onDelete('cascade');
            
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_invoice_items');
        Schema::dropIfExists('cms_invoices');
    }
};
