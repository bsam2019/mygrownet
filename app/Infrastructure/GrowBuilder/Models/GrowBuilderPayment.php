<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBuilderPayment extends Model
{
    protected $table = 'growbuilder_payments';

    protected $fillable = [
        'order_id',
        'site_id',
        'provider',
        'transaction_id',
        'external_reference',
        'amount',
        'currency',
        'phone_number',
        'status',
        'status_message',
        'provider_response',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'provider_response' => 'array',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderOrder::class, 'order_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getAmountInKwachaAttribute(): float
    {
        return $this->amount / 100;
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'K' . number_format($this->amount_in_kwacha, 2);
    }

    public function getProviderLabelAttribute(): string
    {
        return match ($this->provider) {
            'momo' => 'MTN MoMo',
            'airtel' => 'Airtel Money',
            'cash' => 'Cash',
            'bank' => 'Bank Transfer',
            default => ucfirst($this->provider),
        };
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
