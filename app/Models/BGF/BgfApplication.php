<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BgfApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reference_number',
        'business_name',
        'business_description',
        'business_type',
        'order_type',
        'amount_requested',
        'member_contribution',
        'total_project_cost',
        'expected_profit',
        'completion_period_days',
        'documents',
        'order_proof',
        'feasibility_summary',
        'client_name',
        'client_contact',
        'client_verification',
        'tpin',
        'business_account',
        'has_business_experience',
        'previous_projects',
        'score',
        'score_breakdown',
        'evaluator_notes',
        'evaluated_by',
        'evaluated_at',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'documents' => 'array',
        'score_breakdown' => 'array',
        'amount_requested' => 'decimal:2',
        'member_contribution' => 'decimal:2',
        'total_project_cost' => 'decimal:2',
        'expected_profit' => 'decimal:2',
        'has_business_experience' => 'boolean',
        'evaluated_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(BgfEvaluation::class, 'application_id');
    }

    public function project(): HasOne
    {
        return $this->hasOne(BgfProject::class, 'application_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeApproved($query)
    {
        return $this->where('status', 'approved');
    }

    // Helper Methods
    public function generateReferenceNumber(): string
    {
        return 'BGF-APP-' . date('Y') . '-' . str_pad($this->id ?? rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function isEligibleForApproval(): bool
    {
        return $this->score >= 70 && $this->status === 'under_review';
    }

    public function getMemberContributionPercentage(): float
    {
        if ($this->total_project_cost <= 0) {
            return 0;
        }
        return ($this->member_contribution / $this->total_project_cost) * 100;
    }

    public function getExpectedProfitMargin(): float
    {
        if ($this->total_project_cost <= 0) {
            return 0;
        }
        return ($this->expected_profit / $this->total_project_cost) * 100;
    }
}
