<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_client_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('agency_client_invoices')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('agency_client_services')->onDelete('set null');
            $table->text('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('amount', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_id');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_client_invoice_items');
    }
};
