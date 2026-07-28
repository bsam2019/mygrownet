<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceJournalLineModel extends Model
{
    protected $table = 'growfinance_journal_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit_amount',
        'credit_amount',
        'functional_debit_amount',
        'functional_credit_amount',
        'description',
        'dimensions_json',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'functional_debit_amount' => 'decimal:2',
        'functional_credit_amount' => 'decimal:2',
        'dimensions_json' => 'array',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceJournalEntryModel::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceAccountModel::class, 'account_id');
    }
}
