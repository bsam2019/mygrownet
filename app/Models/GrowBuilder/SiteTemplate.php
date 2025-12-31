<?php

namespace App\Models\GrowBuilder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'industry',
        'thumbnail',
        'theme',
        'settings',
        'is_premium',
        'is_active',
        'sort_order',
        'usage_count',
    ];

    protected $casts = [
        'theme' => 'array',
        'settings' => 'array',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(SiteTemplatePage::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    public function scopeByIndustry($query, string $industry)
    {
        return $query->where('industry', $industry);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }
        
        if (str_starts_with($this->thumbnail, 'http')) {
            return $this->thumbnail;
        }
        
        return asset('storage/' . $this->thumbnail);
    }
}
