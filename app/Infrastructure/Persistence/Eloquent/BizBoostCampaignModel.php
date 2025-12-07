<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\BizBoostCampaignFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BizBoostCampaignModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_campaigns';

    protected static function newFactory(): BizBoostCampaignFactory
    {
        return BizBoostCampaignFactory::new();
    }

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'objective',
        'status',
        'start_date',
        'end_date',
        'duration_days',
        'campaign_config',
        'target_platforms',
        'analytics',
        'posts_created',
        'posts_published',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'campaign_config' => 'array',
        'target_platforms' => 'array',
        'analytics' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BizBoostPostModel::class, 'campaign_id');
    }
}
