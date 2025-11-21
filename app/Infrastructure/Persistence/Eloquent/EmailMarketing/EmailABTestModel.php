<?php

namespace App\Infrastructure\Persistence\Eloquent\EmailMarketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailABTestModel extends Model
{
    protected $table = 'email_ab_tests';

    protected $fillable = [
        'campaign_id',
        'name',
        'test_type',
        'variant_a_id',
        'variant_b_id',
        'split_percentage',
        'winner_variant',
        'winner_metric',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignModel::class, 'campaign_id');
    }

    public function variantA(): BelongsTo
    {
        return $this->belongsTo(EmailTemplateModel::class, 'variant_a_id');
    }

    public function variantB(): BelongsTo
    {
        return $this->belongsTo(EmailTemplateModel::class, 'variant_b_id');
    }
}
