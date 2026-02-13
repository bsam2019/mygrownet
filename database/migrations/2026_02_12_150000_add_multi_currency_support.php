<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add base currency to companies
        Schema::table('cms_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_companies', 'base_currency')) {
                $table->string('base_currency', 3)->default('ZMW');
            }
            if (!Schema::hasColumn('cms_companies', 'multi_currency_enabled')) {
                $table->boolean('multi_currency_enabled')->default(false);
            }
        });

        // Create currencies table
        if (!Schema::hasTable('cms_currencies')) {
            Schema::create('cms_currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 3)->unique(); // ISO 4217 code
                $table->string('name');
                $table->string('symbol', 10);
                $table->integer('decimal_places')->default(2);
                $table->string('format')->default('{symbol}{amount}'); // {symbol}{amount} or {amount} {symbol}
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('code');
            });
        }

        // Create exchange rates table
        if (!Schema::hasTable('cms_exchange_rates')) {
            Schema::create('cms_exchange_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                $table->string('from_currency', 3);
                $table->string('to_currency', 3);
                $table->decimal('rate', 20, 10);
                $table->date('effective_date');
                $table->enum('source', ['manual', 'api', 'system'])->default('manual');
                $table->timestamps();
                
                // Use custom shorter index name
                $table->index(['company_id', 'from_currency', 'to_currency', 'effective_date'], 'cms_exch_rates_lookup_idx');
                $table->unique(['company_id', 'from_currency', 'to_currency', 'effective_date'], 'cms_exch_rates_unique_idx');
            });
        }

        // Add currency fields to invoices
        Schema::table('cms_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_invoices', 'currency')) {
                $table->string('currency', 3)->default('ZMW');
            }
            if (!Schema::hasColumn('cms_invoices', 'exchange_rate')) {
                $table->decimal('exchange_rate', 20, 10)->default(1);
            }
        });

        // Add currency fields to payments
        Schema::table('cms_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_payments', 'currency')) {
                $table->string('currency', 3)->default('ZMW');
            }
            if (!Schema::hasColumn('cms_payments', 'exchange_rate')) {
                $table->decimal('exchange_rate', 20, 10)->default(1);
            }
        });

        // Add currency fields to expenses
        Schema::table('cms_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_expenses', 'currency')) {
                $table->string('currency', 3)->default('ZMW');
            }
            if (!Schema::hasColumn('cms_expenses', 'exchange_rate')) {
                $table->decimal('exchange_rate', 20, 10)->default(1);
            }
        });

        // Add currency fields to quotations
        Schema::table('cms_quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_quotations', 'currency')) {
                $table->string('currency', 3)->default('ZMW');
            }
            if (!Schema::hasColumn('cms_quotations', 'exchange_rate')) {
                $table->decimal('exchange_rate', 20, 10)->default(1);
            }
        });

        // Add currency fields to recurring invoices
        Schema::table('cms_recurring_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_recurring_invoices', 'currency')) {
                $table->string('currency', 3)->default('ZMW');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_recurring_invoices', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('cms_quotations', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });

        Schema::table('cms_expenses', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });

        Schema::table('cms_payments', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });

        Schema::table('cms_invoices', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate']);
        });

        Schema::dropIfExists('cms_exchange_rates');
        Schema::dropIfExists('cms_currencies');

        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn(['base_currency', 'multi_currency_enabled']);
        });
    }
};
