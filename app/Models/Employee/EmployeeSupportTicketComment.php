<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSupportTicketComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'author_id',
        'user_id',
        'author_type', // 'employee' or 'support'
        'author_name', // For display when no employee record
        'content',
        'is_internal',
        'attachments',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'attachments' => 'array',
    ];

    protected $appends = ['display_author_name'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(EmployeeSupportTicket::class, 'ticket_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'author_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias for backward compatibility
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'author_id');
    }

    /**
     * Get the display name for the comment author
     */
    public function getDisplayAuthorNameAttribute(): string
    {
        // Priority: stored author_name > employee name > user name > fallback
        if ($this->author_name) {
            return $this->author_name;
        }
        
        if ($this->author) {
            return $this->author->full_name;
        }
        
        if ($this->user) {
            return $this->user->name;
        }
        
        return $this->author_type === 'support' ? 'Support Agent' : 'Unknown';
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }
}
