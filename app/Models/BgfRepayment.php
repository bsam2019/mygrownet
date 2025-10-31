<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BgfRepayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'user_id',
        'repayment_number',
        'total_revenue',
        'total_costs',
        'net_profit',
        'member_share',
        'mygrownet_share',
        'payment_method',
        'transaction_reference',
        'paid_at',
        'status',
        'revenue_breakdown',
        'cost_breakdown',
        'supporting_documents',
        'member_notes',
        'verified_by',
        'verified_at',
        'verification_notes',
        'verified',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'total_costs' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'member_share' => 'decimal:2',
        'mygrownet_share' => 'decimal:2',
        'revenue_breakdown' => 'array',
        'cost_breakdown' => 'array',
        'supporting_documents' => 'array',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'verified' => 'boolean',
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

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopePendingReport($query)
    {
        return $query->where('status', 'pending_report');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Helper Methods
    public function generateRepaymentNumber(): string
    {
        return 'BGF-REP-' . date('Y') . '-' . str_pad($this->id ?? rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function getProfitMargin(): float
    {
        if ($this->total_revenue <= 0) {
            return 0;
        }
        return ($this->net_profit / $this->total_revenue) * 100;
    }
}
