<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoView extends Model
{
    use HasFactory;

    protected $table = 'growstream_video_views';

    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'user_id',
        'watch_duration',
        'completion_percentage',
        'session_id',
        'device_type',
        'browser',
        'os',
        'ip_address',
        'country_code',
        'quality_level',
        'buffering_count',
        'referrer_url',
        'traffic_source',
        'viewed_at',
    ];

    protected $casts = [
        'completion_percentage' => 'decimal:2',
        'viewed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($view) {
            if (empty($view->viewed_at)) {
                $view->viewed_at = now();
            }
        });
    }

    // Relationships
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('viewed_at', '>=', now()->subDays($days));
    }

    public function scopeCompleted($query)
    {
        return $query->where('completion_percentage', '>=', 95);
    }
}
