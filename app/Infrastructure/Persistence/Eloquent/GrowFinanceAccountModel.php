<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\GrowFinance\ValueObjects\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowFinanceAccountModel extends Model
{
    protected $table = 'growfinance_accounts';

    protected $fillable = [
        'business_id',
        'code',
        'name',
        'type',
        'category',
        'description',
        'is_system',
        'is_active',
        'opening_balance',
        'current_balance',
    ];

    protected $casts = [
        'type' => AccountType::class,
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(GrowFinanceJournalLineModel::class, 'account_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(GrowFinanceExpenseModel::class, 'account_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, AccountType $type)
    {
        return $query->where('type', $type->value);
    }
}
