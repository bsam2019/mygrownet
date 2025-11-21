<?php

namespace App\Infrastructure\Persistence\Eloquent\EmailMarketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailQueueModel extends Model
{
    protected $table = 'email_queue';

    protected $fillable = [
        'campaign_id',
        'sequence_id',
        'subscriber_id',
        'user_id',
        'template_id',
        'scheduled_at',
        'sent_at',
        'status',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignModel::class, 'campaign_id');
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(EmailSequenceModel::class, 'sequence_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(CampaignSubscriberModel::class, 'subscriber_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplateModel::class, 'template_id');
    }
}
