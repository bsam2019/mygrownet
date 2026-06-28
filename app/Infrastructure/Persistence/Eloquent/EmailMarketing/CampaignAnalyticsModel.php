<?php

namespace App\Infrastructure\Persistence\Eloquent\EmailMarketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignAnalyticsModel extends Model
{
    protected $table = 'campaign_analytics';

    protected $fillable = [
        'campaign_id',
        'date',
        'emails_sent',
        'emails_delivered',
        'emails_opened',
        'emails_clicked',
        'emails_bounced',
        'emails_unsubscribed',
        'unique_opens',
        'unique_clicks',
        'conversions',
        'revenue',
    ];

    protected $casts = [
        'date' => 'date',
        'revenue' => 'decimal:2',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignModel::class, 'campaign_id');
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->emails_delivered == 0) return 0;
        return round(($this->emails_opened / $this->emails_delivered) * 100, 2);
    }

    public function getClickRateAttribute(): float
    {
        if ($this->emails_delivered == 0) return 0;
        return round(($this->emails_clicked / $this->emails_delivered) * 100, 2);
    }

    public function getConversionRateAttribute(): float
    {
        if ($this->emails_delivered == 0) return 0;
        return round(($this->conversions / $this->emails_delivered) * 100, 2);
    }
}
