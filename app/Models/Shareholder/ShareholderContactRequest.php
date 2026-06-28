<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareholderContactRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'recipient_id',
        'message',
        'status',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'requester_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'recipient_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function accept(string $response = null): void
    {
        $this->update([
            'status' => 'accepted',
            'response' => $response,
            'responded_at' => now(),
        ]);
    }

    public function decline(string $response = null): void
    {
        $this->update([
            'status' => 'declined',
            'response' => $response,
            'responded_at' => now(),
        ]);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            default => ucfirst($this->status),
        };
    }
}
