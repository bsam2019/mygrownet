<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRental extends Model
{
    use HasFactory;

    protected $table = 'growstream_video_rentals';

    protected $fillable = [
        'user_id',
        'video_id',
        'price',
        'currency',
        'access_duration',
        'granted_at',
        'expires_at',
        'provider_reference',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }
}
