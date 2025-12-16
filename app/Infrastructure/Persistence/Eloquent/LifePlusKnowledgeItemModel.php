<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifePlusKnowledgeItemModel extends Model
{
    protected $table = 'lifeplus_knowledge_items';

    protected $fillable = [
        'title',
        'content',
        'category',
        'type',
        'media_url',
        'duration_seconds',
        'is_featured',
        'is_daily_tip',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'is_featured' => 'boolean',
        'is_daily_tip' => 'boolean',
    ];

    public function downloads(): HasMany
    {
        return $this->hasMany(LifePlusUserDownloadModel::class, 'knowledge_item_id');
    }
}
