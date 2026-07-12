<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prime_edge_invoice_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->string('description');
            $table->decimal('unit_price_amount', 12, 2);
            $table->string('unit_price_currency', 3)->default('ZMW');
            $table->integer('quantity')->default(1);
            $table->decimal('total_amount', 12, 2);
            $table->string('total_currency', 3)->default('ZMW');
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('prime_edge_invoices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prime_edge_invoice_line_items');
    }
};
