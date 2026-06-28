<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Database\Factories\EmployeeModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeModel extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return EmployeeModelFactory::new();
    }

    protected $table = 'employees';

    protected $fillable = [
        'employee_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'national_id',
        'department_id',
        'position_id',
        'manager_id',
        'employment_status',
        'hire_date',
        'termination_date',
        'current_salary',
        'emergency_contacts',
        'qualifications',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'current_salary' => 'float',
        'emergency_contacts' => 'array',
        'qualifications' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(PositionModel::class, 'position_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(EmployeeModel::class, 'manager_id');
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(EmployeePerformanceModel::class, 'employee_id');
    }

    public function lastPerformanceReview(): HasOne
    {
        return $this->hasOne(EmployeePerformanceModel::class, 'employee_id')
            ->latest('period_end');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(EmployeeCommissionModel::class, 'employee_id');
    }

    public function clientAssignments(): HasMany
    {
        return $this->hasMany(EmployeeClientAssignmentModel::class, 'employee_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(EmployeePerformanceModel::class, 'reviewer_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('employment_status', 'inactive');
    }

    public function scopeTerminated($query)
    {
        return $query->where('employment_status', 'terminated');
    }

    public function scopeSuspended($query)
    {
        return $query->where('employment_status', 'suspended');
    }

    public function scopeInDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeInPosition($query, int $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    public function scopeWithManager($query, int $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    public function scopeCommissionEligible($query)
    {
        return $query->whereHas('position', function ($query) {
            $query->where(function ($query) {
                $query->where('base_commission_rate', '>', 0)
                      ->orWhere('performance_commission_rate', '>', 0);
            });
        });
    }

    public function scopeHiredBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('hire_date', [$startDate, $endDate]);
    }

    public function scopeWithSalaryRange($query, float $minSalary = null, float $maxSalary = null)
    {
        if ($minSalary !== null) {
            $query->where('current_salary', '>=', $minSalary);
        }
        
        if ($maxSalary !== null) {
            $query->where('current_salary', '<=', $maxSalary);
        }

        return $query;
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->employment_status === 'active';
    }

    public function getYearsOfServiceAttribute(): int
    {
        $endDate = $this->termination_date ?? now();
        return (int) $this->hire_date->diffInYears($endDate);
    }
}