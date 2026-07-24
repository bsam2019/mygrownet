<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('growfinance_customers')->nullOnDelete();
            $table->foreignId('template_id')->nullable();
            $table->string('quotation_number')->unique();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('subject')->nullable(); // Brief description of the quote
            $table->foreignId('converted_invoice_id')->nullable(); // Link to invoice if converted
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'quotation_date']);
            $table->index(['business_id', 'valid_until']);
        });

        Schema::create('growfinance_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('growfinance_quotations')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();

            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_quotation_items');
        Schema::dropIfExists('growfinance_quotations');
    }
};
