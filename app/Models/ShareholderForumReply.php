<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareholderForumReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'author_investor_id',
        'content',
        'status',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ShareholderForumTopic::class, 'topic_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'author_investor_id');
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingModeration($query)
    {
        return $query->where('status', 'pending_moderation');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_moderation';
    }

    public function approve(int $moderatorId): void
    {
        $this->update([
            'status' => 'approved',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);

        // Update topic reply count
        $this->topic->increment('replies_count');
        $this->topic->update(['last_reply_at' => now()]);
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
