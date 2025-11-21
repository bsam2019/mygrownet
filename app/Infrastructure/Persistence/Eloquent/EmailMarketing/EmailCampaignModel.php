<?php

namespace App\Infrastructure\Persistence\Eloquent\EmailMarketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaignModel extends Model
{
    protected $table = 'email_campaigns';

    protected $fillable = [
        'name',
        'type',
        'status',
        'trigger_type',
        'trigger_config',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function sequences(): HasMany
    {
        return $this->hasMany(EmailSequenceModel::class, 'campaign_id');
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(CampaignSubscriberModel::class, 'campaign_id');
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(CampaignAnalyticsModel::class, 'campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function abTests(): HasMany
    {
        return $this->hasMany(EmailABTestModel::class, 'campaign_id');
    }
}
