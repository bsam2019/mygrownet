<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VideoCategory extends Model
{
    use HasFactory;

    protected $table = 'growstream_video_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(VideoCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(VideoCategory::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(
            Video::class,
            'growstream_video_category_pivot',
            'category_id',
            'video_id'
        )->withTimestamps()->withPivot('is_primary');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    // Helper Methods
    public function isParent(): bool
    {
        return $this->children()->exists();
    }

    public function hasParent(): bool
    {
        return !is_null($this->parent_id);
    }
}
