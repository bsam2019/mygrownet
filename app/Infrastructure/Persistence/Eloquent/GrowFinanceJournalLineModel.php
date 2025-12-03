<?php

namespace App\Infrastructure\Persistence\Eloquent;

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
        'description',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
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
