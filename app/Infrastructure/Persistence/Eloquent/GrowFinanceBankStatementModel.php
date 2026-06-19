<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowFinanceBankStatementModel extends Model
{
    protected $table = 'growfinance_bank_statements';

    protected $fillable = [
        'business_id',
        'bank_account_id',
        'statement_period',
        'start_date',
        'end_date',
        'opening_balance',
        'closing_balance',
        'file_name',
        'file_path',
        'line_count',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceBankAccountModel::class, 'bank_account_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(GrowFinanceBankStatementLineModel::class, 'statement_id');
    }
}
