<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SitePost extends Model
{
    protected $table = 'site_posts';

    protected $fillable = [
        'site_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'visibility',
        'published_at',
        'scheduled_at',
        'views_count',
        'comments_enabled',
        'metadata',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'views_count' => 'integer',
        'comments_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(SiteUser::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            SitePostCategory::class,
            'site_post_category',
            'site_post_id',
            'site_post_category_id'
        );
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SiteComment::class, 'post_id');
    }

    // Scopes
    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '>', now());
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeMembersOnly($query)
    {
        return $query->where('visibility', 'members');
    }

    // Helper methods
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isMembersOnly(): bool
    {
        return $this->visibility === 'members';
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public static function generateSlug(string $title, int $siteId, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('site_id', $siteId)
            ->where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
