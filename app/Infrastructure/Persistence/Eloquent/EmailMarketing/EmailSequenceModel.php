<?php

namespace App\Infrastructure\Persistence\Eloquent\EmailMarketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSequenceModel extends Model
{
    protected $table = 'email_sequences';

    protected $fillable = [
        'campaign_id',
        'sequence_order',
        'delay_days',
        'delay_hours',
        'template_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignModel::class, 'campaign_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplateModel::class, 'template_id');
    }
}
