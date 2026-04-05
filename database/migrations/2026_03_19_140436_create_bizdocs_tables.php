<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Business Profiles
        if (!Schema::hasTable('bizdocs_business_profiles')) {
            Schema::create('bizdocs_business_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('business_name');
                $table->string('logo')->nullable();
                $table->text('address');
                $table->string('phone', 50);
                $table->string('email')->nullable();
                $table->string('tpin', 50)->nullable();
                $table->string('website')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_account', 100)->nullable();
                $table->string('bank_branch')->nullable();
                $table->string('default_currency', 3)->default('ZMW');
                $table->string('signature_image')->nullable();
                $table->string('stamp_image')->nullable();
                $table->timestamps();

                $table->index('user_id');
            });
        }

        // Customers
        if (!Schema::hasTable('bizdocs_customers')) {
            Schema::create('bizdocs_customers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizdocs_business_profiles')->onDelete('cascade');
                $table->string('name');
                $table->text('address')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email')->nullable();
                $table->string('tpin', 50)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['business_id', 'phone']);
                $table->index(['business_id', 'email']);
            });
        }

        // Document Templates
        if (!Schema::hasTable('bizdocs_document_templates')) {
            Schema::create('bizdocs_document_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('document_type', [
                    'invoice', 'receipt', 'quotation', 'delivery_note',
                    'proforma_invoice', 'credit_note', 'debit_note', 'purchase_order'
                ]);
                $table->enum('visibility', ['industry', 'business']);
                $table->foreignId('owner_id')->nullable()->constrained('bizdocs_business_profiles')->onDelete('cascade');
                $table->string('industry_category', 100)->nullable();
                $table->json('template_structure');
                $table->string('thumbnail_path')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['visibility', 'document_type']);
                $table->index('owner_id');
                $table->index('industry_category');
            });
        }

        // Documents
        if (!Schema::hasTable('bizdocs_documents')) {
            Schema::create('bizdocs_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizdocs_business_profiles')->onDelete('cascade');
                $table->foreignId('customer_id')->constrained('bizdocs_customers')->onDelete('restrict');
                $table->foreignId('template_id')->nullable()->constrained('bizdocs_document_templates')->onDelete('set null');
                $table->enum('document_type', [
                    'invoice', 'receipt', 'quotation', 'delivery_note',
                    'proforma_invoice', 'credit_note', 'debit_note', 'purchase_order'
                ]);
                $table->string('document_number', 100);
                $table->date('issue_date');
                $table->date('due_date')->nullable();
                $table->date('validity_date')->nullable();
                $table->decimal('subtotal', 15, 2);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->decimal('grand_total', 15, 2);
                $table->string('currency', 3)->default('ZMW');
                $table->string('status', 50);
                $table->text('notes')->nullable();
                $table->text('terms')->nullable();
                $table->text('payment_instructions')->nullable();
                $table->string('pdf_path')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['business_id', 'document_type', 'document_number'], 'unique_doc_number');
                $table->index(['business_id', 'document_type', 'status']);
                $table->index('customer_id');
                $table->index('issue_date');
                $table->index('due_date');
            });
        }

        // Document Items
        if (!Schema::hasTable('bizdocs_document_items')) {
            Schema::create('bizdocs_document_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained('bizdocs_documents')->onDelete('cascade');
                $table->text('description');
                $table->decimal('quantity', 10, 2);
                $table->decimal('unit_price', 15, 2);
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('line_total', 15, 2);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('document_id');
            });
        }

        // Document Payments
        if (!Schema::hasTable('bizdocs_document_payments')) {
            Schema::create('bizdocs_document_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained('bizdocs_documents')->onDelete('cascade');
                $table->date('payment_date');
                $table->decimal('amount', 15, 2);
                $table->string('payment_method', 50);
                $table->string('reference_number', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('document_id');
                $table->index('payment_date');
            });
        }

        // Document Sequences
        if (!Schema::hasTable('bizdocs_document_sequences')) {
            Schema::create('bizdocs_document_sequences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('bizdocs_business_profiles')->onDelete('cascade');
                $table->string('document_type', 50);
                $table->integer('year');
                $table->integer('last_number')->default(0);
                $table->string('prefix', 50);
                $table->integer('padding')->default(4);
                $table->timestamps();

                $table->unique(['business_id', 'document_type', 'year'], 'unique_sequence');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bizdocs_document_sequences');
        Schema::dropIfExists('bizdocs_document_payments');
        Schema::dropIfExists('bizdocs_document_items');
        Schema::dropIfExists('bizdocs_documents');
        Schema::dropIfExists('bizdocs_document_templates');
        Schema::dropIfExists('bizdocs_customers');
        Schema::dropIfExists('bizdocs_business_profiles');
    }
};
