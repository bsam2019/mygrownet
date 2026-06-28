<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusUserDownloadModel extends Model
{
    protected $table = 'lifeplus_user_downloads';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'knowledge_item_id',
        'downloaded_at',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function knowledgeItem(): BelongsTo
    {
        return $this->belongsTo(LifePlusKnowledgeItemModel::class, 'knowledge_item_id');
    }
}
