<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorFeedback extends Model
{
    use HasFactory;

    protected $table = 'investor_feedback';

    protected $fillable = [
        'investor_account_id',
        'feedback_type',
        'category',
        'subject',
        'feedback',
        'satisfaction_rating',
        'status',
        'admin_response',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'satisfaction_rating' => 'integer',
    ];

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'submitted' => 'blue',
            'reviewed' => 'yellow',
            'in_progress' => 'orange',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }
}
