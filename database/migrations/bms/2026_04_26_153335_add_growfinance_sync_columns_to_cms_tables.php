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
        if (Schema::hasTable('cms_invoices') && !Schema::hasColumn('cms_invoices', 'growfinance_synced')) {
            Schema::table('cms_invoices', function (Blueprint $table) {
                $table->boolean('growfinance_synced')->default(false);
                $table->foreignId('growfinance_journal_entry_id')->nullable()->constrained('growfinance_journal_entries')->nullOnDelete();
            });
        }

        // Add sync columns to cms_expenses
        if (Schema::hasTable('cms_expenses') && !Schema::hasColumn('cms_expenses', 'growfinance_synced')) {
            Schema::table('cms_expenses', function (Blueprint $table) {
                $table->boolean('growfinance_synced')->default(false);
                $table->foreignId('growfinance_journal_entry_id')->nullable()->constrained('growfinance_journal_entries')->nullOnDelete();
            });
        }

        // Add sync columns to cms_payments
        if (Schema::hasTable('cms_payments') && !Schema::hasColumn('cms_payments', 'growfinance_synced')) {
            Schema::table('cms_payments', function (Blueprint $table) {
                $table->boolean('growfinance_synced')->default(false);
                $table->foreignId('growfinance_journal_entry_id')->nullable()->constrained('growfinance_journal_entries')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cms_invoices', 'growfinance_synced')) {
            Schema::table('cms_invoices', function (Blueprint $table) {
                $table->dropForeign(['growfinance_journal_entry_id']);
                $table->dropColumn(['growfinance_synced', 'growfinance_journal_entry_id']);
            });
        }

        if (Schema::hasColumn('cms_expenses', 'growfinance_synced')) {
            Schema::table('cms_expenses', function (Blueprint $table) {
                $table->dropForeign(['growfinance_journal_entry_id']);
                $table->dropColumn(['growfinance_synced', 'growfinance_journal_entry_id']);
            });
        }

        if (Schema::hasColumn('cms_payments', 'growfinance_synced')) {
            Schema::table('cms_payments', function (Blueprint $table) {
                $table->dropForeign(['growfinance_journal_entry_id']);
                $table->dropColumn(['growfinance_synced', 'growfinance_journal_entry_id']);
            });
        }
    }
};
