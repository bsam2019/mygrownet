<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tax configuration table
        Schema::create('sa_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('rate', 5, 2)->default(0);
            $table->string('type')->default('inclusive');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['sa_company_id', 'is_default']);
        });

        // Add discount and tax columns to sales
        Schema::table('sa_sales', function (Blueprint $table) {
            $table->decimal('discount_amount', 14, 2)->default(0)->after('total_amount');
            $table->decimal('discount_rate', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_rate');
            $table->unsignedBigInteger('sa_tax_rate_id')->nullable()->after('tax_amount');
            $table->string('currency', 10)->default('ZMW')->after('tax_amount');
        });

        // Add discount and tax to sale items
        Schema::table('sa_sale_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 14, 2)->default(0)->after('total_price');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
        });

        // Add discount and tax columns to invoices
        Schema::table('sa_invoices', function (Blueprint $table) {
            $table->decimal('discount_amount', 14, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
            $table->unsignedBigInteger('sa_tax_rate_id')->nullable()->after('tax_amount');
            $table->string('currency', 10)->default('ZMW')->after('tax_amount');
        });

        Schema::table('sa_invoice_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 14, 2)->default(0)->after('total_price');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
        });

        // Add discount and tax columns to quotations
        Schema::table('sa_quotations', function (Blueprint $table) {
            $table->decimal('discount_amount', 14, 2)->default(0)->after('total_amount');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
            $table->unsignedBigInteger('sa_tax_rate_id')->nullable()->after('tax_amount');
            $table->string('currency', 10)->default('ZMW')->after('tax_amount');
        });

        Schema::table('sa_quotation_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 14, 2)->default(0)->after('total_price');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sa_quotation_items', fn($t) => $t->dropColumn(['discount_amount', 'tax_amount']));
        Schema::table('sa_quotations', fn($t) => $t->dropColumn(['discount_amount', 'tax_amount', 'sa_tax_rate_id', 'currency']));
        Schema::table('sa_invoice_items', fn($t) => $t->dropColumn(['discount_amount', 'tax_amount']));
        Schema::table('sa_invoices', fn($t) => $t->dropColumn(['discount_amount', 'tax_amount', 'sa_tax_rate_id', 'currency']));
        Schema::table('sa_sale_items', fn($t) => $t->dropColumn(['discount_amount', 'tax_amount']));
        Schema::table('sa_sales', fn($t) => $t->dropColumn(['discount_amount', 'discount_rate', 'tax_amount', 'sa_tax_rate_id', 'currency']));
        Schema::dropIfExists('sa_tax_rates');
    }
};
