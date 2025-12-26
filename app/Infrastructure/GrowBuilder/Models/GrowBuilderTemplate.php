<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBuilderTemplate extends Model
{
    protected $table = 'growbuilder_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'preview_image',
        'thumbnail',
        'structure_json',
        'default_styles',
        'is_premium',
        'price',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'structure_json' => 'array',
        'default_styles' => 'array',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'integer',
        'usage_count' => 'integer',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(GrowBuilderSite::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false)->orWhere('price', 0);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true)->where('price', '>', 0);
    }
}
