<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorEarning extends Model
{
    use HasFactory;

    protected $table = 'growstream_creator_earnings';

    protected $fillable = [
        'creator_id',
        'period_start',
        'period_end',
        'premium_watch_seconds',
        'pool_amount',
        'share_percentage',
        'earned_amount',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'premium_watch_seconds' => 'integer',
        'pool_amount' => 'decimal:2',
        'share_percentage' => 'decimal:2',
        'earned_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CreatorProfile::class, 'creator_id');
    }
}
