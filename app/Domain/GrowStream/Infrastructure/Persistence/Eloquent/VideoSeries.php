<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VideoSeries extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'growstream_video_series';

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
        'long_description',
        'poster_url',
        'banner_url',
        'trailer_video_id',
        'total_seasons',
        'total_episodes',
        'series_type',
        'is_ongoing',
        'next_episode_date',
        'creator_id',
        'access_level',
        'is_published',
        'published_at',
        'view_count',
        'subscriber_count',
    ];

    protected $casts = [
        'is_ongoing' => 'boolean',
        'is_published' => 'boolean',
        'next_episode_date' => 'date',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($series) {
            if (empty($series->uuid)) {
                $series->uuid = (string) Str::uuid();
            }
            if (empty($series->slug)) {
                $series->slug = Str::slug($series->title);
            }
        });
    }

    // Relationships
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'series_id')
            ->orderBy('season_number')
            ->orderBy('episode_number');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
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

    public function scopeOngoing($query)
    {
        return $query->where('is_ongoing', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('series_type', $type);
    }

    // Helper Methods
    public function getEpisodesBySeason(int $season)
    {
        return $this->videos()
            ->where('season_number', $season)
            ->orderBy('episode_number')
            ->get();
    }

    public function getSeasons()
    {
        return $this->videos()
            ->select('season_number')
            ->distinct()
            ->orderBy('season_number')
            ->pluck('season_number');
    }

    public function updateEpisodeCount(): void
    {
        $this->total_episodes = $this->videos()->count();
        $this->total_seasons = $this->videos()->max('season_number') ?? 1;
        $this->save();
    }
}
