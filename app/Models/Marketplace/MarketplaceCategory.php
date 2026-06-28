<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceCategory extends Model
{
    protected $table = 'marketplace_categories';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon',
        'image_url',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(MarketplaceProduct::class, 'category_id');
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('status', 'active')->where('stock_quantity', '>', 0);
    }

    /**
     * Get all products including from child categories
     */
    public function allProducts(): HasMany
    {
        return $this->products();
    }

    /**
     * Check if this is a parent/main category
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Scope for parent categories only
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for child categories only
     */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
