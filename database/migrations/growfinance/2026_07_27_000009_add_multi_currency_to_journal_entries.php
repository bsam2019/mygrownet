<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growfinance_journal_entries', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('ZMW')->after('period_id');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000)->after('currency_code');
            $table->decimal('functional_amount', 15, 2)->nullable()->after('exchange_rate');
        });

        Schema::table('growfinance_journal_lines', function (Blueprint $table) {
            $table->decimal('functional_debit_amount', 15, 2)->nullable()->after('credit_amount');
            $table->decimal('functional_credit_amount', 15, 2)->nullable()->after('functional_debit_amount');
        });

        Schema::table('growfinance_accounts', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('ZMW')->after('normal_balance');
        });
    }

    public function down(): void
    {
        Schema::table('growfinance_accounts', function (Blueprint $table) {
            $table->dropColumn('currency_code');
        });

        Schema::table('growfinance_journal_lines', function (Blueprint $table) {
            $table->dropColumn(['functional_debit_amount', 'functional_credit_amount']);
        });

        Schema::table('growfinance_journal_entries', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate', 'functional_amount']);
        });
    }
};
