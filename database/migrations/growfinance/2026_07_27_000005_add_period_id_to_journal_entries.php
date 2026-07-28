<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growfinance_journal_entries', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->constrained('growfinance_accounting_periods')->nullOnDelete();
            $table->index('period_id');
        });
    }

    public function down(): void
    {
        Schema::table('growfinance_journal_entries', function (Blueprint $table) {
            $table->dropIndex(['period_id']);
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });
    }
};
