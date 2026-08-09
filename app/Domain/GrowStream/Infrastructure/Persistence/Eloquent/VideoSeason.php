<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoSeason extends Model
{
    use HasFactory;

    protected $table = 'growstream_video_seasons';

    protected $fillable = [
        'series_id',
        'season_number',
        'title',
        'description',
        'release_year',
        'poster_url',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(VideoSeries::class, 'series_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Video::class, 'season_id')->orderBy('episode_number');
    }
}
