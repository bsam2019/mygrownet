<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Share Transfer Request for Private Limited Company
 * 
 * All share transfers require board approval per Articles of Association.
 * This is NOT a marketplace - it's a formal request process.
 */
class ShareTransferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_investor_id',
        'proposed_buyer_id',
        'proposed_buyer_name',
        'proposed_buyer_email',
        'shares_percentage',
        'proposed_price',
        'approved_price',
        'transfer_type',
        'status',
        'reason_for_sale',
        'board_notes',
        'rejection_reason',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
        'completed_at',
        'required_documents',
        'submitted_documents',
    ];

    protected $casts = [
        'shares_percentage' => 'decimal:4',
        'proposed_price' => 'decimal:2',
        'approved_price' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
        'required_documents' => 'array',
        'submitted_documents' => 'array',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'seller_investor_id');
    }

    public function proposedBuyer(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'proposed_buyer_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopePendingBoard($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'board_approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'board_rejected';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'submitted']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft';
    }

    public function submit(): void
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted for Review',
            'under_review' => 'Under Board Review',
            'board_approved' => 'Board Approved',
            'board_rejected' => 'Board Rejected',
            'completed' => 'Transfer Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'blue',
            'under_review' => 'yellow',
            'board_approved' => 'green',
            'board_rejected' => 'red',
            'completed' => 'emerald',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
}
