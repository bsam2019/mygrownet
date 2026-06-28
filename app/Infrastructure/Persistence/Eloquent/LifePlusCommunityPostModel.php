<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusCommunityPostModel extends Model
{
    protected $table = 'lifeplus_community_posts';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'location',
        'event_date',
        'image_url',
        'is_promoted',
        'expires_at',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_promoted' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
