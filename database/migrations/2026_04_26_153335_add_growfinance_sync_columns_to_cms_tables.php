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
        // Add sync columns to cms_invoices
        Schema::table('cms_invoices', function (Blueprint $table) {
            $table->boolean('growfinance_synced')->default(false)->after('status');
            $table->foreignId('growfinance_journal_entry_id')->nullable()->after('growfinance_synced')->constrained('growfinance_journal_entries')->nullOnDelete();
        });

        // Add sync columns to cms_expenses
        Schema::table('cms_expenses', function (Blueprint $table) {
            $table->boolean('growfinance_synced')->default(false)->after('status');
            $table->foreignId('growfinance_journal_entry_id')->nullable()->after('growfinance_synced')->constrained('growfinance_journal_entries')->nullOnDelete();
        });

        // Add sync columns to cms_payments
        Schema::table('cms_payments', function (Blueprint $table) {
            $table->boolean('growfinance_synced')->default(false)->after('status');
            $table->foreignId('growfinance_journal_entry_id')->nullable()->after('growfinance_synced')->constrained('growfinance_journal_entries')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_invoices', function (Blueprint $table) {
            $table->dropForeign(['growfinance_journal_entry_id']);
            $table->dropColumn(['growfinance_synced', 'growfinance_journal_entry_id']);
        });

        Schema::table('cms_expenses', function (Blueprint $table) {
            $table->dropForeign(['growfinance_journal_entry_id']);
            $table->dropColumn(['growfinance_synced', 'growfinance_journal_entry_id']);
        });

        Schema::table('cms_payments', function (Blueprint $table) {
            $table->dropForeign(['growfinance_journal_entry_id']);
            $table->dropColumn(['growfinance_synced', 'growfinance_journal_entry_id']);
        });
    }
};
