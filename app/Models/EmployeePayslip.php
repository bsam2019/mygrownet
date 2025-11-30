<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payslip_number',
        'pay_period_start',
        'pay_period_end',
        'payment_date',
        'basic_salary',
        'overtime_pay',
        'bonus',
        'commission',
        'allowances',
        'gross_pay',
        'tax',
        'pension',
        'health_insurance',
        'loan_deduction',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'earnings_breakdown',
        'deductions_breakdown',
        'notes',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'commission' => 'decimal:2',
        'allowances' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'tax' => 'decimal:2',
        'pension' => 'decimal:2',
        'health_insurance' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'earnings_breakdown' => 'array',
        'deductions_breakdown' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('payment_date', $year);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month);
    }

    public function getPayPeriodAttribute(): string
    {
        return $this->pay_period_start->format('M d') . ' - ' . $this->pay_period_end->format('M d, Y');
    }
}
