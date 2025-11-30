<?php

namespace App\Infrastructure\Persistence\Eloquent\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\InvestorAccount;

class TicketCommentModel extends Model
{
    protected $table = 'ticket_comments';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'investor_account_id',
        'author_type',
        'author_name',
        'comment',
        'is_internal',
        'read_by_user',
        'read_by_admin',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'read_by_user' => 'boolean',
        'read_by_admin' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicketModel::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'investor_account_id');
    }

    /**
     * Alias for user relationship - used for author access
     * This provides backward compatibility for code expecting an 'author' relationship
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the display name for the comment author
     */
    public function getDisplayAuthorNameAttribute(): string
    {
        // Use stored author_name if available
        if ($this->author_name) {
            return $this->author_name;
        }
        
        // Otherwise try to get from relationships
        if ($this->author_type === 'investor' && $this->investorAccount) {
            return $this->investorAccount->name;
        }
        
        if ($this->user) {
            return $this->user->name;
        }
        
        return $this->is_internal ? 'Support' : 'Unknown';
    }
}
