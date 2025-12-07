<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BizBoostCategoryModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_categories';

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(BizBoostProductModel::class, 'category_id');
    }

    /**
     * Get product count for this category (including legacy string-based categories).
     */
    public function getProductCountAttribute(): int
    {
        // Count products with this category_id OR legacy category name
        return BizBoostProductModel::where('business_id', $this->business_id)
            ->where(function ($query) {
                $query->where('category_id', $this->id)
                    ->orWhere('category', $this->name);
            })
            ->count();
    }
}
