<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentureDividendModel extends Model
{
    protected $table = 'venture_dividends';

    protected $fillable = [
        'venture_id',
        'shareholder_id',
        'dividend_period',
        'declaration_date',
        'payment_date',
        'amount',
        'equity_percentage_at_payment',
        'payment_method',
        'payment_reference',
        'status',
        'paid_at',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'declaration_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'equity_percentage_at_payment' => 'decimal:4',
        'paid_at' => 'datetime',
    ];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(VentureModel::class, 'venture_id');
    }

    public function shareholder(): BelongsTo
    {
        return $this->belongsTo(VentureShareholderModel::class, 'shareholder_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['declared', 'processing']);
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('dividend_period', $period);
    }

    // Helpers
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['declared', 'processing']);
    }
}
