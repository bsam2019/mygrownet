<?php

namespace App\Models\GrowStart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerProvider extends Model
{
    protected $table = 'growstart_partner_providers';

    protected $fillable = [
        'name',
        'category',
        'description',
        'contact_phone',
        'contact_email',
        'website',
        'province',
        'city',
        'country_id',
        'is_featured',
        'is_verified',
        'rating',
        'review_count',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
        'rating' => 'decimal:1',
        'review_count' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByProvince($query, string $province)
    {
        return $query->where('province', $province);
    }

    public function scopeForCountry($query, int $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeHighRated($query, float $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating);
    }
}
