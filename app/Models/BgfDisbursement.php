<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BgfDisbursement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'user_id',
        'disbursement_number',
        'amount',
        'type',
        'purpose',
        'milestone_reference',
        'payment_method',
        'recipient_name',
        'recipient_account',
        'recipient_phone',
        'vendor_details',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'transaction_reference',
        'disbursed_at',
        'disbursement_notes',
        'documents',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'documents' => 'array',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(BgfProject::class, 'project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper Methods
    public function generateDisbursementNumber(): string
    {
        return 'BGF-DIS-' . date('Y') . '-' . str_pad($this->id ?? rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function isDirectVendorPayment(): bool
    {
        return $this->type === 'direct_vendor';
    }
}
