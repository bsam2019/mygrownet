<?php

namespace App\Infrastructure\Persistence\Eloquent\Wedding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WeddingBookingModel extends Model
{
    use HasFactory;

    protected $table = 'wedding_bookings';

    protected $fillable = [
        'wedding_event_id',
        'wedding_vendor_id',
        'service_type',
        'service_date',
        'service_time',
        'quoted_amount',
        'final_amount',
        'deposit_amount',
        'status',
        'requirements',
        'vendor_notes',
        'contract_terms',
        'confirmed_at'
    ];

    protected $casts = [
        'service_date' => 'date',
        'service_time' => 'datetime',
        'quoted_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'contract_terms' => 'array',
        'confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function weddingEvent(): BelongsTo
    {
        return $this->belongsTo(WeddingEventModel::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(WeddingVendorModel::class, 'wedding_vendor_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(WeddingReviewModel::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'deposit_paid', 'paid']);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'quoted']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}