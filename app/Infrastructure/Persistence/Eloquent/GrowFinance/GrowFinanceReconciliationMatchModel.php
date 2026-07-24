<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceReconciliationMatchModel extends Model
{
    protected $table = 'growfinance_reconciliation_matches';

    protected $fillable = [
        'reconciliation_period_id',
        'statement_line_id',
        'journal_line_id',
        'statement_amount',
        'journal_amount',
        'match_type',
    ];

    protected $casts = [
        'statement_amount' => 'decimal:2',
        'journal_amount' => 'decimal:2',
    ];

    public function reconciliationPeriod(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceReconciliationPeriodModel::class, 'reconciliation_period_id');
    }

    public function statementLine(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceBankStatementLineModel::class, 'statement_line_id');
    }

    public function journalLine(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceJournalLineModel::class, 'journal_line_id');
    }
}
