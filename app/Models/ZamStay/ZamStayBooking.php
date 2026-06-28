<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ZamStayBooking extends Model
{
    protected $table = 'zamstay_bookings';

    protected $fillable = [
        'property_id',
        'agent_id',
        'user_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'guests',
        'special_requests',
        'payment_method',
        'payment_reference',
        'payment_phone',
        'transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'decimal:2',
        'guests' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(ZamStayProperty::class, 'property_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(ZamStayAgent::class, 'agent_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(ZamStayReview::class, 'booking_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
