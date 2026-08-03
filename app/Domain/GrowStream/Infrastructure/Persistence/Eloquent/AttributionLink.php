<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AttributionLink extends Model
{
    use HasFactory;

    protected $table = 'growstream_attribution_events';

    protected $fillable = [
        'uuid',
        'creator_id',
        'source',
        'visitor_session_id',
        'converted_user_id',
        'watch_minutes_attributed',
    ];

    protected $casts = [
        'watch_minutes_attributed' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($link) {
            if (empty($link->uuid)) {
                $link->uuid = (string) Str::uuid();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class, 'creator_id');
    }

    public function convertedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_user_id');
    }
}
