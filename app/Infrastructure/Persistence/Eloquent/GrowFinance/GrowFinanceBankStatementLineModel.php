<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceBankStatementLineModel extends Model
{
    protected $table = 'growfinance_bank_statement_lines';

    protected $fillable = [
        'statement_id',
        'transaction_date',
        'description',
        'reference',
        'debit_amount',
        'credit_amount',
        'running_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'running_balance' => 'decimal:2',
    ];

    public function statement(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceBankStatementModel::class, 'statement_id');
    }

    public function getAmountAttribute(): float
    {
        return $this->credit_amount > 0 ? $this->credit_amount : -$this->debit_amount;
    }
}
