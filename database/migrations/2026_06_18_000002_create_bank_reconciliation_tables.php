<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bank Statements (imported CSV/manual)
        if (!Schema::hasTable('growfinance_bank_statements')) {
            Schema::create('growfinance_bank_statements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('bank_account_id')->constrained('growfinance_bank_accounts')->cascadeOnDelete();
                $table->string('statement_period')->nullable(); // e.g. "June 2026"
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->decimal('closing_balance', 15, 2)->default(0);
                $table->string('file_name')->nullable(); // original file name
                $table->string('file_path')->nullable(); // storage path
                $table->integer('line_count')->default(0);
                $table->string('status')->default('draft'); // draft, imported, reconciled
                $table->timestamps();

                $table->index(['business_id', 'bank_account_id']);
            });
        }

        // Bank Statement Lines (individual transactions from statements)
        if (!Schema::hasTable('growfinance_bank_statement_lines')) {
            Schema::create('growfinance_bank_statement_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('statement_id')->constrained('growfinance_bank_statements')->cascadeOnDelete();
                $table->date('transaction_date');
                $table->string('description');
                $table->string('reference')->nullable();
                $table->decimal('debit_amount', 15, 2)->default(0);
                $table->decimal('credit_amount', 15, 2)->default(0);
                $table->decimal('running_balance', 15, 2)->nullable();
                $table->string('status')->default('unmatched'); // unmatched, matched, ignored
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['statement_id', 'status']);
            });
        }

        // Reconciliation Periods
        if (!Schema::hasTable('growfinance_reconciliation_periods')) {
            Schema::create('growfinance_reconciliation_periods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('bank_account_id')->constrained('growfinance_bank_accounts')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->decimal('closing_balance', 15, 2)->default(0);
                $table->decimal('book_balance', 15, 2)->default(0);
                $table->decimal('difference', 15, 2)->default(0);
                $table->string('status')->default('in_progress'); // in_progress, completed
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('completed_by')->nullable()->constrained('users');
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'bank_account_id', 'status']);
            });
        }

        // Reconciliation Matches (linking statement lines to journal entries)
        if (!Schema::hasTable('growfinance_reconciliation_matches')) {
            Schema::create('growfinance_reconciliation_matches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reconciliation_period_id')->constrained('growfinance_reconciliation_periods')->cascadeOnDelete();
                $table->foreignId('statement_line_id')->constrained('growfinance_bank_statement_lines')->cascadeOnDelete();
                $table->foreignId('journal_line_id')->constrained('growfinance_journal_lines')->cascadeOnDelete();
                $table->decimal('statement_amount', 15, 2);
                $table->decimal('journal_amount', 15, 2);
                $table->string('match_type')->default('manual'); // auto, manual
                $table->timestamps();

                $table->unique(['statement_line_id', 'journal_line_id']);
                $table->index(['reconciliation_period_id']);
            });
        }

        // Add reconciled flag to journal lines
        if (Schema::hasTable('growfinance_journal_lines') && !Schema::hasColumn('growfinance_journal_lines', 'reconciled')) {
            Schema::table('growfinance_journal_lines', function (Blueprint $table) {
                $table->boolean('reconciled')->default(false)->after('description');
                $table->timestamp('reconciled_at')->nullable()->after('reconciled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('growfinance_journal_lines', 'reconciled')) {
            Schema::table('growfinance_journal_lines', function (Blueprint $table) {
                $table->dropColumn(['reconciled', 'reconciled_at']);
            });
        }
        Schema::dropIfExists('growfinance_reconciliation_matches');
        Schema::dropIfExists('growfinance_reconciliation_periods');
        Schema::dropIfExists('growfinance_bank_statement_lines');
        Schema::dropIfExists('growfinance_bank_statements');
    }
};
