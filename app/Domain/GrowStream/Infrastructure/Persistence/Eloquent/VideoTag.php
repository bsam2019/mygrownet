<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class VideoTag extends Model
{
    use HasFactory;

    protected $table = 'growstream_video_tags';

    protected $fillable = [
        'name',
        'slug',
        'usage_count',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Relationships
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(
            Video::class,
            'growstream_video_tag_pivot',
            'tag_id',
            'video_id'
        )->withTimestamps();
    }

    // Helper Methods
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function decrementUsage(): void
    {
        $this->decrement('usage_count');
    }
}
