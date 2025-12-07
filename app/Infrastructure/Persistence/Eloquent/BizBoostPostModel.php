<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\BizBoostPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BizBoostPostModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_posts';

    protected static function newFactory(): BizBoostPostFactory
    {
        return BizBoostPostFactory::new();
    }

    protected $fillable = [
        'business_id',
        'title',
        'caption',
        'status',
        'scheduled_at',
        'published_at',
        'platform_targets',
        'external_ids',
        'analytics',
        'post_type',
        'template_id',
        'campaign_id',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'platform_targets' => 'array',
        'external_ids' => 'array',
        'analytics' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(BizBoostPostMediaModel::class, 'post_id')->orderBy('sort_order');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(BizBoostTemplateModel::class, 'template_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BizBoostCampaignModel::class, 'campaign_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeReadyToPublish($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }
}
