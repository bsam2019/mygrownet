<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BgfProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_id',
        'user_id',
        'project_number',
        'approved_amount',
        'member_contribution',
        'member_profit_percentage',
        'mygrownet_profit_percentage',
        'expected_completion_date',
        'contract_url',
        'contract_signed_at',
        'status',
        'total_disbursed',
        'total_repaid',
        'actual_profit',
        'member_profit_share',
        'mygrownet_profit_share',
        'actual_completion_date',
        'completed_on_time',
        'performance_rating',
        'completion_notes',
        'milestones',
        'milestones_completed',
    ];

    protected $casts = [
        'approved_amount' => 'decimal:2',
        'member_contribution' => 'decimal:2',
        'total_disbursed' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'actual_profit' => 'decimal:2',
        'member_profit_share' => 'decimal:2',
        'mygrownet_profit_share' => 'decimal:2',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'contract_signed_at' => 'datetime',
        'completed_on_time' => 'boolean',
        'milestones' => 'array',
    ];

    // Relationships
    public function application(): BelongsTo
    {
        return $this->belongsTo(BgfApplication::class, 'application_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(BgfDisbursement::class, 'project_id');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(BgfRepayment::class, 'project_id');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(BgfContract::class, 'project_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper Methods
    public function generateProjectNumber(): string
    {
        return 'BGF-PRJ-' . date('Y') . '-' . str_pad($this->id ?? rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function getRemainingAmount(): float
    {
        return $this->approved_amount - $this->total_disbursed;
    }

    public function getDisbursementProgress(): float
    {
        if ($this->approved_amount <= 0) {
            return 0;
        }
        return ($this->total_disbursed / $this->approved_amount) * 100;
    }

    public function isOverdue(): bool
    {
        return $this->expected_completion_date < now() && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function calculateProfitShares(float $netProfit): array
    {
        $memberShare = $netProfit * ($this->member_profit_percentage / 100);
        $mygrownetShare = $netProfit * ($this->mygrownet_profit_percentage / 100);

        return [
            'member_share' => round($memberShare, 2),
            'mygrownet_share' => round($mygrownetShare, 2),
        ];
    }
}
