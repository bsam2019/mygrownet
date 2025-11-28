<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ShareholderForumTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'author_investor_id',
        'title',
        'slug',
        'content',
        'status',
        'is_pinned',
        'is_announcement',
        'views_count',
        'replies_count',
        'last_reply_at',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_announcement' => 'boolean',
        'last_reply_at' => 'datetime',
        'moderated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($topic) {
            if (empty($topic->slug)) {
                $topic->slug = Str::slug($topic->title) . '-' . Str::random(6);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShareholderForumCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'author_investor_id');
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ShareholderForumReply::class, 'topic_id');
    }

    public function approvedReplies(): HasMany
    {
        return $this->replies()->where('status', 'approved');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingModeration($query)
    {
        return $query->where('status', 'pending_moderation');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_moderation';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function approve(int $moderatorId): void
    {
        $this->update([
            'status' => 'approved',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);
    }

    public function reject(int $moderatorId): void
    {
        $this->update([
            'status' => 'rejected',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);
    }
}
