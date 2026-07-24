<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->date('sale_date');
            $table->time('sale_time')->nullable();
            $table->enum('payment_method', ['cash', 'mobile_money', 'card', 'credit', 'transfer'])->default('cash');
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('amount_tendered', 16, 2)->default(0);
            $table->decimal('change_due', 16, 2)->default(0);
            $table->foreignId('sold_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sa_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_sale_id')->constrained('sa_sales')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_sale_items');
        Schema::dropIfExists('sa_sales');
    }
};
