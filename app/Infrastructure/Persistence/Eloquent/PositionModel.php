<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\PositionModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PositionModel extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return PositionModelFactory::new();
    }

    protected $table = 'positions';

    protected $fillable = [
        'title',
        'description',
        'department_id',
        'min_salary',
        'max_salary',
        'base_commission_rate',
        'performance_commission_rate',
        'permissions',
        'level',
        'is_active',
    ];

    protected $casts = [
        'min_salary' => 'float',
        'max_salary' => 'float',
        'base_commission_rate' => 'float',
        'performance_commission_rate' => 'float',
        'permissions' => 'array',
        'level' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(EmployeeModel::class, 'position_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCommissionEligible($query)
    {
        return $query->where(function ($query) {
            $query->where('base_commission_rate', '>', 0)
                  ->orWhere('performance_commission_rate', '>', 0);
        });
    }

    public function scopeInDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeWithSalaryRange($query, float $minSalary = null, float $maxSalary = null)
    {
        if ($minSalary !== null) {
            $query->where('min_salary', '>=', $minSalary);
        }
        
        if ($maxSalary !== null) {
            $query->where('max_salary', '<=', $maxSalary);
        }

        return $query;
    }

    public function scopeWithEmployeeCount($query)
    {
        return $query->withCount('employees');
    }
}