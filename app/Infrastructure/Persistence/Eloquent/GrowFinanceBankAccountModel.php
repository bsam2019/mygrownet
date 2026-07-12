<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrowFinanceBankAccountModel extends Model
{
    use SoftDeletes;

    protected $table = 'growfinance_bank_accounts';

    protected $fillable = [
        'business_id',
        'account_name',
        'account_number',
        'bank_name',
        'bank_branch',
        'account_type',
        'currency',
        'opening_balance',
        'current_balance',
        'is_default',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_id');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(GrowFinanceBankStatementModel::class, 'bank_account_id');
    }

    public function reconciliationPeriods(): HasMany
    {
        return $this->hasMany(GrowFinanceReconciliationPeriodModel::class, 'bank_account_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
