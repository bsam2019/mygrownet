<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('cms_customers');
            $table->string('quotation_number', 50);
            $table->date('quotation_date');
            $table->date('expiry_date')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->foreignId('converted_to_job_id')->nullable()->constrained('cms_jobs');
            $table->foreignId('created_by')->constrained('cms_users');
            $table->foreignId('approved_by')->nullable()->constrained('cms_users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'quotation_number'], 'unique_quotation_number');
            $table->index(['company_id', 'status']);
            $table->index('customer_id');
            $table->index(['company_id', 'expiry_date']);
        });

        Schema::create('cms_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('cms_quotations')->onDelete('cascade');
            $table->string('description', 255);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();

            $table->index('quotation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_quotation_items');
        Schema::dropIfExists('cms_quotations');
    }
};
