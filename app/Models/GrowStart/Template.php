<?php

namespace App\Models\GrowStart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    protected $table = 'growstart_templates';

    protected $fillable = [
        'name',
        'description',
        'category',
        'file_path',
        'file_type',
        'industry_id',
        'country_id',
        'is_premium',
        'download_count',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'download_count' => 'integer',
    ];

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    public function scopeForCountry($query, ?int $countryId)
    {
        return $query->where(function ($q) use ($countryId) {
            $q->whereNull('country_id')
              ->orWhere('country_id', $countryId);
        });
    }

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }
}
