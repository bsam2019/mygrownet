<?php

namespace App\Infrastructure\Persistence\Eloquent\ZamStay;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZamStayPropertyModel extends Model
{
    protected $table = 'zamstay_properties';

    protected $fillable = [
        'owner_id',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'price_per_night',
        'status',
        'images',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'amenities',
        'property_type',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'amenities' => 'array',
        'price_per_night' => 'decimal:2',
        'is_active' => 'boolean',
        'max_guests' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ZamStayBookingModel::class, 'property_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ZamStayReviewModel::class, 'property_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
