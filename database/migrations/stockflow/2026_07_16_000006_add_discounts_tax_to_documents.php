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

        // Apply columns one at a time to avoid "column already exists" on re-run
        $this->addColumnIfMissing('sa_sales', 'discount_amount', function ($t) { $t->decimal('discount_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_sales', 'discount_rate', function ($t) { $t->decimal('discount_rate', 5, 2)->default(0); });
        $this->addColumnIfMissing('sa_sales', 'tax_amount', function ($t) { $t->decimal('tax_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_sales', 'sa_tax_rate_id', function ($t) { $t->unsignedBigInteger('sa_tax_rate_id')->nullable(); });
        $this->addColumnIfMissing('sa_sales', 'currency', function ($t) { $t->string('currency', 10)->default('ZMW'); });

        $this->addColumnIfMissing('sa_sale_items', 'discount_amount', function ($t) { $t->decimal('discount_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_sale_items', 'tax_amount', function ($t) { $t->decimal('tax_amount', 14, 2)->default(0); });

        $this->addColumnIfMissing('sa_invoices', 'discount_amount', function ($t) { $t->decimal('discount_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_invoices', 'tax_amount', function ($t) { $t->decimal('tax_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_invoices', 'sa_tax_rate_id', function ($t) { $t->unsignedBigInteger('sa_tax_rate_id')->nullable(); });
        $this->addColumnIfMissing('sa_invoices', 'currency', function ($t) { $t->string('currency', 10)->default('ZMW'); });

        $this->addColumnIfMissing('sa_invoice_items', 'discount_amount', function ($t) { $t->decimal('discount_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_invoice_items', 'tax_amount', function ($t) { $t->decimal('tax_amount', 14, 2)->default(0); });

        $this->addColumnIfMissing('sa_quotations', 'discount_amount', function ($t) { $t->decimal('discount_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_quotations', 'tax_amount', function ($t) { $t->decimal('tax_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_quotations', 'sa_tax_rate_id', function ($t) { $t->unsignedBigInteger('sa_tax_rate_id')->nullable(); });
        $this->addColumnIfMissing('sa_quotations', 'currency', function ($t) { $t->string('currency', 10)->default('ZMW'); });

        $this->addColumnIfMissing('sa_quotation_items', 'discount_amount', function ($t) { $t->decimal('discount_amount', 14, 2)->default(0); });
        $this->addColumnIfMissing('sa_quotation_items', 'tax_amount', function ($t) { $t->decimal('tax_amount', 14, 2)->default(0); });
    }

    private function addColumnIfMissing(string $table, string $column, callable $callback): void
    {
        if (!Schema::hasColumn($table, $column)) {
            Schema::table($table, function (Blueprint $t) use ($callback) {
                $callback($t);
            });
        }
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
