<?php

namespace App\Infrastructure\Persistence\Eloquent\Wedding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class WeddingReviewModel extends Model
{
    use HasFactory;

    protected $table = 'wedding_reviews';

    protected $fillable = [
        'wedding_vendor_id',
        'wedding_booking_id',
        'user_id',
        'rating',
        'review',
        'images',
        'verified',
        'service_date'
    ];

    protected $casts = [
        'rating' => 'integer',
        'images' => 'array',
        'verified' => 'boolean',
        'service_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(WeddingVendorModel::class, 'wedding_vendor_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(WeddingBookingModel::class, 'wedding_booking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4);
    }
}