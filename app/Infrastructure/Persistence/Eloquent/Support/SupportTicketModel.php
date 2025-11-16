<?php

namespace App\Infrastructure\Persistence\Eloquent\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class SupportTicketModel extends Model
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'user_id',
        'category',
        'priority',
        'status',
        'subject',
        'description',
        'assigned_to',
        'resolved_at',
        'closed_at',
        'satisfaction_rating',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'satisfaction_rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketCommentModel::class, 'ticket_id');
    }
}
