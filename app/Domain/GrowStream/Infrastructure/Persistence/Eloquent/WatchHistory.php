<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchHistory extends Model
{
    use HasFactory;

    protected $table = 'growstream_watch_history';

    protected $fillable = [
        'user_id',
        'video_id',
        'current_position',
        'duration',
        'progress_percentage',
        'is_completed',
        'completed_at',
        'session_id',
        'device_type',
        'ip_address',
        'user_agent',
        'started_at',
        'last_watched_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'progress_percentage' => 'decimal:2',
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'last_watched_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    // Scopes
    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false)
            ->where('progress_percentage', '>', 0);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_watched_at', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function updateProgress(int $currentPosition, int $duration): void
    {
        $this->current_position = $currentPosition;
        $this->duration = $duration;
        $this->progress_percentage = $duration > 0 ? ($currentPosition / $duration) * 100 : 0;
        $this->last_watched_at = now();

        // Mark as completed if watched 95% or more
        if ($this->progress_percentage >= config('growstream.player.completion_threshold', 95)) {
            $this->is_completed = true;
            $this->completed_at = now();
        }

        $this->save();
    }

    public function canContinue(): bool
    {
        return !$this->is_completed && $this->progress_percentage > 0;
    }
}
