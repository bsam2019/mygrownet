<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sa_tax_rates')) {
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
        }

        Schema::table('sa_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_sales', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('sa_sales', 'discount_rate')) {
                $table->decimal('discount_rate', 5, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('sa_sales', 'tax_amount')) {
                $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_rate');
            }
            if (!Schema::hasColumn('sa_sales', 'sa_tax_rate_id')) {
                $table->unsignedBigInteger('sa_tax_rate_id')->nullable()->after('tax_amount');
            }
            if (!Schema::hasColumn('sa_sales', 'currency')) {
                $table->string('currency', 10)->default('ZMW')->after('tax_amount');
            }
        });

        Schema::table('sa_sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_sale_items', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('sa_sale_items', 'tax_amount')) {
                $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
            }
        });

        Schema::table('sa_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('sa_invoices', 'tax_amount')) {
                $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('sa_invoices', 'sa_tax_rate_id')) {
                $table->unsignedBigInteger('sa_tax_rate_id')->nullable()->after('tax_amount');
            }
            if (!Schema::hasColumn('sa_invoices', 'currency')) {
                $table->string('currency', 10)->default('ZMW')->after('tax_amount');
            }
        });

        Schema::table('sa_invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_invoice_items', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('sa_invoice_items', 'tax_amount')) {
                $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
            }
        });

        Schema::table('sa_quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_quotations', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('sa_quotations', 'tax_amount')) {
                $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('sa_quotations', 'sa_tax_rate_id')) {
                $table->unsignedBigInteger('sa_tax_rate_id')->nullable()->after('tax_amount');
            }
            if (!Schema::hasColumn('sa_quotations', 'currency')) {
                $table->string('currency', 10)->default('ZMW')->after('tax_amount');
            }
        });

        Schema::table('sa_quotation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sa_quotation_items', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('sa_quotation_items', 'tax_amount')) {
                $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
            }
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
