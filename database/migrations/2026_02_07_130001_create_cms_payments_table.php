<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('cms_customers')->onDelete('restrict');
            $table->foreignId('invoice_id')->nullable()->constrained('cms_invoices')->onDelete('restrict');
            
            $table->string('payment_number', 50)->unique();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            
            $table->enum('payment_method', ['cash', 'mobile_money', 'bank_transfer', 'cheque', 'card'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('received_by')->constrained('cms_users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['company_id', 'payment_date']);
            $table->index(['company_id', 'customer_id']);
            $table->index('payment_method');
        });

        Schema::create('cms_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('cms_payments')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('cms_invoices')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->unique(['payment_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_payment_allocations');
        Schema::dropIfExists('cms_payments');
    }
};
