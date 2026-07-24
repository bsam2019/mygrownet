<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('quotation_number')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->date('quotation_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'declined', 'expired', 'converted'])->default('draft');
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_to_sale_id')->nullable()->constrained('sa_sales')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sa_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_quotation_id')->constrained('sa_quotations')->cascadeOnDelete();
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
        Schema::dropIfExists('sa_quotation_items');
        Schema::dropIfExists('sa_quotations');
    }
};
