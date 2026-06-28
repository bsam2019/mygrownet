<?php

namespace App\Infrastructure\Persistence\Eloquent\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\InvestorAccount;

class SupportTicketModel extends Model
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'user_id',
        'investor_account_id',
        'category',
        'source',
        'priority',
        'status',
        'subject',
        'description',
        'assigned_to',
        'resolved_at',
        'closed_at',
        'closed_by',
        'closure_reason',
        'satisfaction_rating',
        'rating_feedback',
        'rated_at',
        'user_last_read_at',
        'admin_last_read_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'rated_at' => 'datetime',
        'user_last_read_at' => 'datetime',
        'admin_last_read_at' => 'datetime',
        'satisfaction_rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'investor_account_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketCommentModel::class, 'ticket_id');
    }

    /**
     * Get the display name for the ticket creator
     */
    public function getCreatorNameAttribute(): string
    {
        if ($this->source === 'investor' && $this->investorAccount) {
            return $this->investorAccount->name;
        }
        return $this->user?->name ?? 'Unknown';
    }

    /**
     * Scope for investor tickets
     */
    public function scopeInvestorTickets($query)
    {
        return $query->where('source', 'investor');
    }

    /**
     * Scope for member tickets
     */
    public function scopeMemberTickets($query)
    {
        return $query->where('source', 'member');
    }

    /**
     * Get count of unread comments for admin
     */
    public function getUnreadAdminCountAttribute(): int
    {
        return $this->comments()
            ->where('read_by_admin', false)
            ->where('author_type', '!=', 'support')
            ->count();
    }

    /**
     * Get count of unread comments for user
     */
    public function getUnreadUserCountAttribute(): int
    {
        return $this->comments()
            ->where('read_by_user', false)
            ->where('author_type', 'support')
            ->count();
    }

    /**
     * Check if ticket has unread messages for admin
     */
    public function hasUnreadForAdmin(): bool
    {
        return $this->unread_admin_count > 0;
    }

    /**
     * Check if ticket has unread messages for user
     */
    public function hasUnreadForUser(): bool
    {
        return $this->unread_user_count > 0;
    }

    /**
     * Mark all comments as read by admin
     */
    public function markAsReadByAdmin(): void
    {
        $this->comments()->where('read_by_admin', false)->update(['read_by_admin' => true]);
        $this->update(['admin_last_read_at' => now()]);
    }

    /**
     * Mark all comments as read by user
     */
    public function markAsReadByUser(): void
    {
        $this->comments()->where('read_by_user', false)->update(['read_by_user' => true]);
        $this->update(['user_last_read_at' => now()]);
    }

    /**
     * Submit rating for the ticket
     */
    public function submitRating(int $rating, ?string $feedback = null): void
    {
        $this->update([
            'satisfaction_rating' => $rating,
            'rating_feedback' => $feedback,
            'rated_at' => now(),
        ]);
    }

    /**
     * Check if ticket can be rated
     */
    public function canBeRated(): bool
    {
        return in_array($this->status, ['resolved', 'closed']) && is_null($this->rated_at);
    }
}
