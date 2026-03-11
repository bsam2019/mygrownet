<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Watchlist extends Model
{
    use HasFactory;

    protected $table = 'growstream_watchlists';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'watchlistable_type',
        'watchlistable_id',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($watchlist) {
            if (empty($watchlist->added_at)) {
                $watchlist->added_at = now();
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function watchlistable(): MorphTo
    {
        return $this->morphTo();
    }
}
