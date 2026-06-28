<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanReceivableModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_loans_receivable';

    protected $fillable = [
        'company_id',
        'user_id',
        'loan_number',
        'loan_type',
        'principal_amount',
        'interest_rate',
        'total_amount',
        'amount_paid',
        'principal_paid',
        'interest_paid',
        'outstanding_balance',
        'term_months',
        'monthly_payment',
        'disbursement_date',
        'due_date',
        'next_payment_date',
        'last_payment_date',
        'status',
        'days_overdue',
        'risk_category',
        'purpose',
        'notes',
        'approved_by',
        'disbursed_by',
        'approved_at',
        'fully_paid_at',
        'defaulted_at',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'principal_paid' => 'decimal:2',
        'interest_paid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'disbursement_date' => 'date',
        'due_date' => 'date',
        'next_payment_date' => 'date',
        'last_payment_date' => 'date',
        'approved_at' => 'datetime',
        'fully_paid_at' => 'datetime',
        'defaulted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepaymentModel::class, 'loan_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LoanScheduleModel::class, 'loan_id');
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'active' 
            && $this->next_payment_date 
            && $this->next_payment_date->isPast();
    }

    /**
     * Check if loan is in default
     */
    public function isInDefault(): bool
    {
        return $this->status === 'defaulted' || $this->days_overdue > 90;
    }

    /**
     * Get payment progress percentage
     */
    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_amount <= 0) {
            return 0;
        }
        return ($this->amount_paid / $this->total_amount) * 100;
    }

    /**
     * Scope: Active loans
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Overdue loans
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('next_payment_date')
            ->where('next_payment_date', '<', now());
    }

    /**
     * Scope: By risk category
     */
    public function scopeByRisk($query, string $category)
    {
        return $query->where('risk_category', $category);
    }

    /**
     * Scope: For company
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
