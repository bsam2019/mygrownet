<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'growstream_videos';

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
        'long_description',
        'video_provider',
        'provider_video_id',
        'video_url',
        'playback_url',
        'playback_policy',
        'upload_status',
        'upload_progress',
        'processing_started_at',
        'processing_completed_at',
        'duration',
        'file_size',
        'resolution',
        'aspect_ratio',
        'thumbnail_url',
        'poster_url',
        'banner_url',
        'trailer_video_id',
        'content_type',
        'language',
        'subtitles_available',
        'access_level',
        'is_published',
        'published_at',
        'is_featured',
        'featured_at',
        'is_downloadable',
        'series_id',
        'season_number',
        'episode_number',
        'creator_id',
        'content_rating',
        'quality_rating',
        'skill_level',
        'view_count',
        'unique_viewers',
        'total_watch_time',
        'average_watch_duration',
        'completion_rate',
        'like_count',
        'share_count',
        'meta_title',
        'meta_description',
        'keywords',
        'watch_points',
        'completion_points',
        'share_points',
        'watch_lp',
        'completion_lp',
        'share_lp',
        'is_starter_kit_content',
        'starter_kit_tier',
        'starter_kit_unlock_order',
        'starter_kit_points_reward',
    ];

    protected $casts = [
        'subtitles_available' => 'array',
        'keywords' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_downloadable' => 'boolean',
        'is_starter_kit_content' => 'boolean',
        'published_at' => 'datetime',
        'featured_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
        'quality_rating' => 'decimal:1',
        'completion_rate' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($video) {
            if (empty($video->uuid)) {
                $video->uuid = (string) Str::uuid();
            }
            if (empty($video->slug)) {
                $video->slug = Str::slug($video->title);
            }
        });
    }

    // Relationships
    public function series(): BelongsTo
    {
        return $this->belongsTo(VideoSeries::class, 'series_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class, 'creator_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            VideoCategory::class,
            'growstream_video_category_pivot',
            'video_id',
            'category_id'
        )->withTimestamps()->withPivot('is_primary');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            VideoTag::class,
            'growstream_video_tag_pivot',
            'video_id',
            'tag_id'
        )->withTimestamps();
    }

    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class, 'video_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(VideoView::class, 'video_id');
    }

    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'trailer_video_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFree($query)
    {
        return $query->where('access_level', 'free');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeReady($query)
    {
        return $query->where('upload_status', 'ready');
    }

    public function scopeTrending($query, int $days = 7)
    {
        return $query->published()
            ->withCount(['views' => function ($q) use ($days) {
                $q->where('viewed_at', '>=', now()->subDays($days));
            }])
            ->orderByDesc('views_count');
    }

    public function scopeByContentType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    public function scopeStarterKitContent($query)
    {
        return $query->where('is_starter_kit_content', true);
    }

    public function scopeByStarterKitTier($query, string $tier)
    {
        return $query->where('starter_kit_tier', $tier)->orWhere('starter_kit_tier', 'all');
    }

    // Helper Methods
    public function isPublished(): bool
    {
        return $this->is_published 
            && $this->published_at 
            && $this->published_at->isPast();
    }

    public function isReady(): bool
    {
        return $this->upload_status === 'ready';
    }

    public function isFree(): bool
    {
        return $this->access_level === 'free';
    }

    public function isPartOfSeries(): bool
    {
        return !is_null($this->series_id);
    }

    public function getNextEpisode(): ?self
    {
        if (!$this->isPartOfSeries()) {
            return null;
        }

        return static::where('series_id', $this->series_id)
            ->where('season_number', $this->season_number)
            ->where('episode_number', '>', $this->episode_number)
            ->orderBy('episode_number')
            ->first();
    }

    public function getPreviousEpisode(): ?self
    {
        if (!$this->isPartOfSeries()) {
            return null;
        }

        return static::where('series_id', $this->series_id)
            ->where('season_number', $this->season_number)
            ->where('episode_number', '<', $this->episode_number)
            ->orderByDesc('episode_number')
            ->first();
    }

    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return '0:00';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function isStarterKitContent(): bool
    {
        return $this->is_starter_kit_content;
    }

    public function getStarterKitTierLabel(): string
    {
        return match($this->starter_kit_tier) {
            'basic' => 'Basic Tier',
            'premium' => 'Premium Tier', 
            'elite' => 'Elite Tier',
            'all' => 'All Tiers',
            default => 'Not Set',
        };
    }

    public function getTotalPointsReward(): int
    {
        return $this->watch_points + $this->completion_points + $this->share_points + $this->starter_kit_points_reward;
    }
}
