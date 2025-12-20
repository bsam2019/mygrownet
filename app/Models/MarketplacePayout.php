<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplacePayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'status',
        'method',
        'account_number',
        'account_name',
        'reference_number',
        'failure_reason',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = ['formatted_amount', 'status_label', 'method_label'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'K' . number_format($this->amount / 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match($this->method) {
            'momo' => 'MTN MoMo',
            'airtel' => 'Airtel Money',
            'zamtel' => 'Zamtel Kwacha',
            'bank' => 'Bank Transfer',
            default => ucfirst($this->method),
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
