<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BizBoostTemplateModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_templates';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'industry',
        'template_data',
        'thumbnail_path',
        'preview_path',
        'width',
        'height',
        'is_premium',
        'is_active',
        'is_featured',
        'usage_count',
        'sort_order',
    ];

    protected $casts = [
        'template_data' => 'array',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Get template_data with defaults.
     */
    public function getTemplateDataAttribute($value): array
    {
        $data = is_string($value) ? json_decode($value, true) : $value;
        
        return array_merge([
            'caption' => '',
            'hashtags' => [],
            'cta' => '',
        ], $data ?? []);
    }

    public function customTemplates(): HasMany
    {
        return $this->hasMany(BizBoostCustomTemplateModel::class, 'base_template_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BizBoostPostModel::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByIndustry($query, string $industry)
    {
        return $query->where('industry', $industry);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
