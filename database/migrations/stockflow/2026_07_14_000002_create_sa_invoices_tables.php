<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled', 'partially_paid'])->default('draft');
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('amount_paid', 16, 2)->default(0);
            $table->decimal('balance_due', 16, 2)->default(0);
            $table->string('payment_terms', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sa_quotation_id')->nullable()->constrained('sa_quotations')->nullOnDelete();
            $table->foreignId('sa_sale_id')->nullable()->constrained('sa_sales')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sa_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_invoice_id')->constrained('sa_invoices')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->nullable()->constrained('sa_items')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_invoice_items');
        Schema::dropIfExists('sa_invoices');
    }
};
