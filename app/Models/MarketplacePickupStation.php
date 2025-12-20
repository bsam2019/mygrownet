<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketplacePickupStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'province',
        'district',
        'address',
        'phone',
        'email',
        'latitude',
        'longitude',
        'operating_hours',
        'is_active',
        'capacity',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected $appends = ['full_address'];

    public function getFullAddressAttribute(): string
    {
        return "{$this->address}, {$this->district}, {$this->province}";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInProvince($query, string $province)
    {
        return $query->where('province', $province);
    }

    public function scopeInDistrict($query, string $district)
    {
        return $query->where('district', $district);
    }
}
