<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowFinanceReconciliationPeriodModel extends Model
{
    protected $table = 'growfinance_reconciliation_periods';

    protected $fillable = [
        'business_id',
        'bank_account_id',
        'start_date',
        'end_date',
        'opening_balance',
        'closing_balance',
        'book_balance',
        'difference',
        'status',
        'created_by',
        'completed_by',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'book_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceBankAccountModel::class, 'bank_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GrowFinanceReconciliationMatchModel::class, 'reconciliation_period_id');
    }
}
