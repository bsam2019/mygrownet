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
        'organizational_level',
        'reports_to_position_id',
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

    // Organizational structure relationships
    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(PositionModel::class, 'reports_to_position_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(PositionModel::class, 'reports_to_position_id');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(\App\Models\PositionKpi::class, 'position_id');
    }

    public function responsibilities(): HasMany
    {
        return $this->hasMany(\App\Models\PositionResponsibility::class, 'position_id');
    }

    public function hiringRoadmap(): HasMany
    {
        return $this->hasMany(\App\Models\HiringRoadmap::class, 'position_id');
    }

    // Scopes for organizational levels
    public function scopeCLevel($query)
    {
        return $query->where('organizational_level', 'c_level');
    }

    public function scopeDirector($query)
    {
        return $query->where('organizational_level', 'director');
    }

    public function scopeManager($query)
    {
        return $query->where('organizational_level', 'manager');
    }

    public function scopeTeamLead($query)
    {
        return $query->where('organizational_level', 'team_lead');
    }

    public function scopeIndividual($query)
    {
        return $query->where('organizational_level', 'individual');
    }
}