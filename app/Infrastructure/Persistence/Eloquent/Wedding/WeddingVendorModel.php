<?php

namespace App\Infrastructure\Persistence\Eloquent\Wedding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class WeddingVendorModel extends Model
{
    use HasFactory;

    protected $table = 'wedding_vendors';

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'category',
        'contact_person',
        'phone',
        'email',
        'location',
        'description',
        'price_range',
        'rating',
        'review_count',
        'verified',
        'featured',
        'services',
        'portfolio_images',
        'availability'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'review_count' => 'integer',
        'verified' => 'boolean',
        'featured' => 'boolean',
        'services' => 'array',
        'portfolio_images' => 'array',
        'availability' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(WeddingBookingModel::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(WeddingReviewModel::class);
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4.0);
    }
}