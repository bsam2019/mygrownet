<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_invoice_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->index();
            $table->enum('document_type', ['invoice', 'delivery_note', 'quotation', 'receipt']);
            $table->string('document_number')->unique();
            
            // Business Info
            $table->string('business_name');
            $table->text('business_address')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_logo')->nullable();
            $table->string('business_tax_number')->nullable();
            $table->string('business_website')->nullable();
            
            // Client Info
            $table->string('client_name');
            $table->text('client_address')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();
            
            // Dates
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            
            // Financial
            $table->string('currency', 3)->default('ZMW');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // Additional
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
            
            // Template & Styling
            $table->string('template')->default('classic');
            $table->json('colors')->nullable();
            $table->string('signature')->nullable();
            
            $table->timestamps();
            
            $table->index(['session_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('quick_invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('document_id')
                ->references('id')
                ->on('quick_invoice_documents')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_invoice_items');
        Schema::dropIfExists('quick_invoice_documents');
    }
};
