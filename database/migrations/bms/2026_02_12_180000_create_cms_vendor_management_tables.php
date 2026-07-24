<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vendors table
        if (!Schema::hasTable('cms_vendors')) {
            Schema::create('cms_vendors', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('vendor_number')->unique();
                        
                        // Basic info
                        $table->string('name');
                        $table->string('email')->nullable();
                        $table->string('phone')->nullable();
                        $table->string('contact_person')->nullable();
                        
                        // Address
                        $table->text('address')->nullable();
                        $table->string('city')->nullable();
                        $table->string('country')->default('Zambia');
                        
                        // Business details
                        $table->string('tax_number')->nullable();
                        $table->string('registration_number')->nullable();
                        $table->enum('vendor_type', ['supplier', 'contractor', 'service_provider', 'other'])->default('supplier');
                        
                        // Payment terms
                        $table->integer('payment_terms_days')->default(30);
                        $table->string('payment_method')->nullable();
                        $table->string('bank_name')->nullable();
                        $table->string('bank_account_number')->nullable();
                        $table->string('mobile_money_number')->nullable();
                        
                        // Status
                        $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
                        $table->text('notes')->nullable();
                        
                        // Performance tracking
                        $table->decimal('total_spent', 12, 2)->default(0);
                        $table->integer('total_orders')->default(0);
                        $table->decimal('average_rating', 3, 2)->default(0);
                        
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'status']);
                        $table->index(['company_id', 'vendor_type']);
                    });
        }

        // Purchase orders table
        if (!Schema::hasTable('cms_purchase_orders')) {
            Schema::create('cms_purchase_orders', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('po_number')->unique();
                        
                        // Vendor
                        $table->foreignId('vendor_id')->constrained('cms_vendors')->onDelete('restrict');
                        
                        // PO details
                        $table->date('po_date');
                        $table->date('expected_delivery_date')->nullable();
                        $table->date('actual_delivery_date')->nullable();
                        
                        // Status
                        $table->enum('status', ['draft', 'pending_approval', 'approved', 'sent', 'partially_received', 'received', 'cancelled'])->default('draft');
                        
                        // Amounts
                        $table->decimal('subtotal', 10, 2)->default(0);
                        $table->decimal('tax_amount', 10, 2)->default(0);
                        $table->decimal('total_amount', 10, 2)->default(0);
                        
                        // Currency
                        $table->string('currency_code', 3)->default('ZMW');
                        $table->decimal('exchange_rate', 10, 4)->default(1);
                        
                        // Delivery
                        $table->text('delivery_address')->nullable();
                        $table->text('notes')->nullable();
                        $table->text('terms_conditions')->nullable();
                        
                        // Approval
                        $table->foreignId('approved_by')->nullable()->constrained('cms_users')->onDelete('set null');
                        $table->timestamp('approved_at')->nullable();
                        
                        // Tracking
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'status']);
                        $table->index(['company_id', 'vendor_id']);
                        $table->index(['company_id', 'po_date']);
                    });
        }

        // Purchase order items table
        if (!Schema::hasTable('cms_purchase_order_items')) {
            Schema::create('cms_purchase_order_items', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('purchase_order_id')->constrained('cms_purchase_orders')->onDelete('cascade');
                        
                        // Item details
                        $table->foreignId('inventory_item_id')->nullable()->constrained('cms_inventory_items')->onDelete('set null');
                        $table->string('description');
                        $table->decimal('quantity', 10, 2);
                        $table->string('unit')->default('pcs');
                        $table->decimal('unit_price', 10, 2);
                        $table->decimal('total_price', 10, 2);
                        
                        // Receiving
                        $table->decimal('quantity_received', 10, 2)->default(0);
                        $table->decimal('quantity_pending', 10, 2)->default(0);
                        
                        $table->timestamps();
                        
                        $table->index('purchase_order_id');
                    });
        }

        // Goods received notes table
        if (!Schema::hasTable('cms_goods_received_notes')) {
            Schema::create('cms_goods_received_notes', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('grn_number')->unique();
                        
                        $table->foreignId('purchase_order_id')->constrained('cms_purchase_orders')->onDelete('restrict');
                        $table->foreignId('vendor_id')->constrained('cms_vendors')->onDelete('restrict');
                        
                        $table->date('received_date');
                        $table->string('received_by_name');
                        $table->text('notes')->nullable();
                        $table->text('discrepancies')->nullable();
                        
                        $table->enum('status', ['draft', 'completed'])->default('draft');
                        
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'purchase_order_id']);
                    });
        }

        // GRN items table
        if (!Schema::hasTable('cms_grn_items')) {
            Schema::create('cms_grn_items', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('grn_id')->constrained('cms_goods_received_notes')->onDelete('cascade');
                        $table->foreignId('po_item_id')->constrained('cms_purchase_order_items')->onDelete('restrict');
                        
                        $table->decimal('quantity_received', 10, 2);
                        $table->decimal('quantity_rejected', 10, 2)->default(0);
                        $table->text('rejection_reason')->nullable();
                        $table->text('notes')->nullable();
                        
                        $table->timestamps();
                    });
        }

        // Vendor invoices table
        if (!Schema::hasTable('cms_vendor_invoices')) {
            Schema::create('cms_vendor_invoices', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('invoice_number');
                        
                        $table->foreignId('vendor_id')->constrained('cms_vendors')->onDelete('restrict');
                        $table->foreignId('purchase_order_id')->nullable()->constrained('cms_purchase_orders')->onDelete('set null');
                        
                        $table->date('invoice_date');
                        $table->date('due_date');
                        
                        $table->decimal('subtotal', 10, 2);
                        $table->decimal('tax_amount', 10, 2)->default(0);
                        $table->decimal('total_amount', 10, 2);
                        $table->decimal('amount_paid', 10, 2)->default(0);
                        $table->decimal('balance', 10, 2);
                        
                        $table->enum('status', ['pending', 'approved', 'paid', 'overdue', 'cancelled'])->default('pending');
                        
                        $table->text('notes')->nullable();
                        $table->json('attachments')->nullable();
                        
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'vendor_id']);
                        $table->index(['company_id', 'status']);
                        $table->index(['company_id', 'due_date']);
                    });
        }

        // Vendor payments table
        if (!Schema::hasTable('cms_vendor_payments')) {
            Schema::create('cms_vendor_payments', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('payment_number')->unique();
                        
                        $table->foreignId('vendor_id')->constrained('cms_vendors')->onDelete('restrict');
                        $table->foreignId('vendor_invoice_id')->nullable()->constrained('cms_vendor_invoices')->onDelete('set null');
                        
                        $table->date('payment_date');
                        $table->decimal('amount', 10, 2);
                        $table->string('payment_method');
                        $table->string('reference_number')->nullable();
                        
                        $table->text('notes')->nullable();
                        
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'vendor_id']);
                        $table->index(['company_id', 'payment_date']);
                    });
        }

        // Vendor performance ratings table
        if (!Schema::hasTable('cms_vendor_ratings')) {
            Schema::create('cms_vendor_ratings', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->foreignId('vendor_id')->constrained('cms_vendors')->onDelete('cascade');
                        $table->foreignId('purchase_order_id')->nullable()->constrained('cms_purchase_orders')->onDelete('set null');
                        
                        // Ratings (1-5)
                        $table->integer('quality_rating')->nullable();
                        $table->integer('delivery_rating')->nullable();
                        $table->integer('communication_rating')->nullable();
                        $table->integer('pricing_rating')->nullable();
                        $table->decimal('overall_rating', 3, 2);
                        
                        $table->text('comments')->nullable();
                        
                        $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
                        $table->timestamps();
                        
                        $table->index(['company_id', 'vendor_id']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_vendor_ratings');
        Schema::dropIfExists('cms_vendor_payments');
        Schema::dropIfExists('cms_vendor_invoices');
        Schema::dropIfExists('cms_grn_items');
        Schema::dropIfExists('cms_goods_received_notes');
        Schema::dropIfExists('cms_purchase_order_items');
        Schema::dropIfExists('cms_purchase_orders');
        Schema::dropIfExists('cms_vendors');
    }
};
