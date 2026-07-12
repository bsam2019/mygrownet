<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class BizBoostAdCampaignModel extends Model
{
    protected $table = 'bizboost_ad_campaigns';

    protected $fillable = [
        'user_id',
        'business_id',
        'name',
        'objective',
        'client_budget',
        'meta_budget',
        'platform_markup',
        'meta_campaign_id',
        'meta_ad_set_id',
        'meta_ad_id',
        'status',
        'start_date',
        'end_date',
        'duration_days',
        'targeting',
        'creatives',
        'insights',
        'error_message',
    ];

    protected $casts = [
        'client_budget' => 'decimal:4',
        'meta_budget' => 'decimal:4',
        'platform_markup' => 'decimal:4',
        'targeting' => 'json',
        'creatives' => 'json',
        'insights' => 'json',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
