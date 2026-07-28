<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowFinanceJournalEntryModel extends Model
{
    protected $table = 'growfinance_journal_entries';

    protected $fillable = [
        'business_id',
        'journal_number',
        'date',
        'description',
        'reference',
        'status',
        'reversal_of_id',
        'reversal_reason',
        'source_event_id',
        'period_id',
        'currency_code',
        'exchange_rate',
        'functional_amount',
        'created_by',
        'posted_at',
        'dimensions_json',
    ];

    protected $casts = [
        'date' => 'date',
        'posted_at' => 'datetime',
        'exchange_rate' => 'decimal:4',
        'functional_amount' => 'decimal:2',
        'dimensions_json' => 'array',
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

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversal_of_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeInDateRange($query, string $start, string $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }
}
