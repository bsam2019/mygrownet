<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteComment extends Model
{
    protected $table = 'site_comments';

    protected $fillable = [
        'site_id',
        'post_id',
        'site_user_id',
        'parent_id',
        'author_name',
        'author_email',
        'content',
        'status',
        'ip_address',
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(SitePost::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(SiteUser::class, 'site_user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // Scopes
    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // Helper methods
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSpam(): bool
    {
        return $this->status === 'spam';
    }

    public function isGuest(): bool
    {
        return $this->site_user_id === null;
    }

    public function getAuthorName(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->author_name ?? 'Anonymous';
    }

    public function approve(): void
    {
        $this->update(['status' => 'approved']);
    }

    public function markAsSpam(): void
    {
        $this->update(['status' => 'spam']);
    }

    public function trash(): void
    {
        $this->update(['status' => 'trash']);
    }

    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }
}
