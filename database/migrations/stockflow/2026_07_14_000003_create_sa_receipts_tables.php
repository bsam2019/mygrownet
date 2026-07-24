<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->foreignId('sa_sale_id')->nullable()->constrained('sa_sales')->nullOnDelete();
            $table->foreignId('sa_invoice_id')->nullable()->constrained('sa_invoices')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->date('receipt_date');
            $table->enum('payment_method', ['cash', 'mobile_money', 'card', 'credit', 'transfer'])->default('cash');
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('amount_received', 16, 2)->default(0);
            $table->decimal('change_due', 16, 2)->default(0);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sa_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_receipt_id')->constrained('sa_receipts')->cascadeOnDelete();
            $table->string('item_description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_receipt_items');
        Schema::dropIfExists('sa_receipts');
    }
};
