<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorSubscription extends Model
{
    use HasFactory;

    protected $table = 'growstream_creator_subscriptions';

    protected $fillable = [
        'user_id',
        'creator_id',
        'price_monthly',
        'currency',
        'status',
        'started_at',
        'expires_at',
        'cancelled_at',
        'provider_reference',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class, 'creator_id');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
