<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Database\Factories\EmployeeCommissionModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeCommissionModel extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return EmployeeCommissionModelFactory::new();
    }

    protected $table = 'employee_commissions';

    protected $fillable = [
        'employee_id',
        'commission_type',
        'source_reference',
        'amount',
        'commission_amount', // Alias for amount
        'rate_applied',
        'earned_date',
        'calculation_date', // Alias for earned_date
        'payment_date',
        'payment_status',
        'status', // Alias for payment_status
        'description',
        'calculation_details',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'commission_amount' => 'float',
        'rate_applied' => 'float',
        'earned_date' => 'date',
        'calculation_date' => 'date',
        'payment_date' => 'date',
        'calculation_details' => 'array',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Note: Investment relationship would be added when Investment model is available
    // public function investment(): BelongsTo
    // {
    //     return $this->belongsTo(InvestmentModel::class, 'investment_id');
    // }

    // Scopes
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('commission_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('payment_status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('earned_date', [$startDate, $endDate]);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['pending', 'approved']);
    }

    public function scopeOrderByAmount($query, string $direction = 'desc')
    {
        return $query->orderBy('amount', $direction);
    }

    public function scopeOrderByDate($query, string $direction = 'desc')
    {
        return $query->orderBy('earned_date', $direction);
    }

    // Accessors and Mutators to keep columns in sync
    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = $value;
        $this->attributes['commission_amount'] = $value;
    }

    public function setCommissionAmountAttribute($value): void
    {
        $this->attributes['commission_amount'] = $value;
        $this->attributes['amount'] = $value;
    }

    public function setPaymentStatusAttribute($value): void
    {
        $this->attributes['payment_status'] = $value;
        $this->attributes['status'] = $value;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = $value;
        $this->attributes['payment_status'] = $value;
    }

    public function setEarnedDateAttribute($value): void
    {
        $this->attributes['earned_date'] = $value;
        $this->attributes['calculation_date'] = $value;
    }

    public function setCalculationDateAttribute($value): void
    {
        $this->attributes['calculation_date'] = $value;
        $this->attributes['earned_date'] = $value;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getIsApprovedAttribute(): bool
    {
        return in_array($this->payment_status, ['approved', 'paid']);
    }

    public function getCommissionTypeDisplayAttribute(): string
    {
        return match ($this->commission_type) {
            'base' => 'Base Commission',
            'performance' => 'Performance Commission',
            'bonus' => 'Bonus',
            'referral' => 'Referral Commission',
            default => ucfirst(str_replace('_', ' ', $this->commission_type))
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Pending Payment',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->payment_status)
        };
    }
}