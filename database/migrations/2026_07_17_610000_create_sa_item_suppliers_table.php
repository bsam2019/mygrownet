<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_item_suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->foreignId('sa_supplier_id')->constrained('sa_suppliers')->cascadeOnDelete();
            $table->string('supplier_sku')->nullable();
            $table->decimal('supplier_price', 12, 2)->nullable();
            $table->integer('lead_time_days')->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->timestamps();

            $table->unique(['sa_item_id', 'sa_supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_item_suppliers');
    }
};
