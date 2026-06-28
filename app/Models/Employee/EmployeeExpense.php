<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'approved_by',
        'title',
        'description',
        'category',
        'amount',
        'currency',
        'expense_date',
        'status',
        'receipts',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'reimbursed_at',
        'reimbursement_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'receipts' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'reimbursed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeReimbursed($query)
    {
        return $query->where('status', 'reimbursed');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('expense_date', $year);
    }
}
