<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBuilderPage extends Model
{
    protected $table = 'growbuilder_pages';

    protected $fillable = [
        'site_id',
        'title',
        'slug',
        'content_json',
        'meta_title',
        'meta_description',
        'og_image',
        'is_homepage',
        'is_published',
        'show_in_nav',
        'nav_order',
    ];

    protected $casts = [
        'content_json' => 'array',
        'is_homepage' => 'boolean',
        'is_published' => 'boolean',
        'show_in_nav' => 'boolean',
        'nav_order' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeInNav($query)
    {
        return $query->where('show_in_nav', true)->orderBy('nav_order');
    }

    public function scopeHomepage($query)
    {
        return $query->where('is_homepage', true);
    }

    public function getEffectiveMetaTitleAttribute(): string
    {
        return $this->meta_title ?? $this->title;
    }

    /**
     * Alias for content_json for easier access
     */
    public function getContentAttribute(): ?array
    {
        return $this->content_json;
    }
}
