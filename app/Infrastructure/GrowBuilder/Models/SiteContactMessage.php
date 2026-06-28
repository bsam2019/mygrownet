<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteContactMessage extends Model
{
    protected $fillable = [
        'site_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'reply',
        'replied_at',
        'replied_by',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function repliedByUser(): BelongsTo
    {
        return $this->belongsTo(SiteUser::class, 'replied_by');
    }

    public function scopeForSite($query, int $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeNotArchived($query)
    {
        return $query->where('status', '!=', 'archived');
    }

    public function markAsRead(): void
    {
        if ($this->status === 'unread') {
            $this->update(['status' => 'read']);
        }
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }
}
