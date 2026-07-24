<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sa_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_supplier_id')->nullable()->constrained('sa_suppliers')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->enum('status', ['draft', 'ordered', 'partial', 'received', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('tax', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sa_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_purchase_order_id')->constrained('sa_purchase_orders')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->decimal('quantity_ordered', 12, 2)->default(0);
            $table->decimal('quantity_received', 12, 2)->default(0);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('total_cost', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_purchase_order_items');
        Schema::dropIfExists('sa_purchase_orders');
        Schema::dropIfExists('sa_suppliers');
    }
};
