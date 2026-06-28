<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowFinanceJournalEntryModel extends Model
{
    protected $table = 'growfinance_journal_entries';

    protected $fillable = [
        'business_id',
        'entry_number',
        'entry_date',
        'description',
        'reference',
        'is_posted',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_posted' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(GrowFinanceJournalLineModel::class, 'journal_entry_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }

    public function getTotalDebitsAttribute(): float
    {
        return $this->lines->sum('debit_amount');
    }

    public function getTotalCreditsAttribute(): float
    {
        return $this->lines->sum('credit_amount');
    }

    public function isBalanced(): bool
    {
        return abs($this->total_debits - $this->total_credits) < 0.01;
    }
}
