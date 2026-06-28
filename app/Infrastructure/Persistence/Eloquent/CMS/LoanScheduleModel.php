<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanScheduleModel extends Model
{
    protected $table = 'cms_loan_schedules';

    protected $fillable = [
        'loan_id',
        'installment_number',
        'due_date',
        'installment_amount',
        'principal_portion',
        'interest_portion',
        'amount_paid',
        'status',
        'paid_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'installment_amount' => 'decimal:2',
        'principal_portion' => 'decimal:2',
        'interest_portion' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoanReceivableModel::class, 'loan_id');
    }

    /**
     * Check if installment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }
}
