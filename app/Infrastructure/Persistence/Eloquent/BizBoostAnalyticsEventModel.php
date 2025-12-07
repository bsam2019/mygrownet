<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostAnalyticsEventModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_analytics_events';

    protected $fillable = [
        'business_id',
        'event_type',
        'source',
        'post_id',
        'payload',
        'ip_address',
        'user_agent',
        'referrer',
        'recorded_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
