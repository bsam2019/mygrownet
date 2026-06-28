<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentureInvestmentModel extends Model
{
    use SoftDeletes;

    protected $table = 'venture_investments';

    protected $fillable = [
        'venture_id',
        'user_id',
        'amount',
        'shares_allocated',
        'equity_percentage',
        'status',
        'payment_method',
        'payment_reference',
        'payment_confirmed_at',
        'is_shareholder',
        'shareholder_registered_at',
        'shareholder_certificate_number',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'shares_allocated' => 'decimal:6',
        'equity_percentage' => 'decimal:4',
        'payment_confirmed_at' => 'datetime',
        'is_shareholder' => 'boolean',
        'shareholder_registered_at' => 'date',
        'processed_at' => 'datetime',
    ];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(VentureModel::class, 'venture_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function shareholder(): BelongsTo
    {
        return $this->belongsTo(VentureShareholderModel::class, 'id', 'investment_id');
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->whereIn('status', ['confirmed', 'completed']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helpers
    public function isConfirmed(): bool
    {
        return in_array($this->status, ['confirmed', 'completed']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}
