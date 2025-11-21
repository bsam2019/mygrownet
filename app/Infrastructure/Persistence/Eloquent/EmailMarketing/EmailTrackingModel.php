<?php

namespace App\Infrastructure\Persistence\Eloquent\EmailMarketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTrackingModel extends Model
{
    protected $table = 'email_tracking';

    protected $fillable = [
        'queue_id',
        'user_id',
        'campaign_id',
        'event_type',
        'event_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'event_data' => 'array',
    ];

    public function queueItem(): BelongsTo
    {
        return $this->belongsTo(EmailQueueModel::class, 'queue_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignModel::class, 'campaign_id');
    }
}
