<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\DepartmentModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return DepartmentModelFactory::new();
    }

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'description',
        'head_employee_id',
        'parent_department_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function headEmployee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'head_employee_id');
    }

    public function head(): BelongsTo
    {
        return $this->headEmployee();
    }

    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'parent_department_id');
    }

    public function childDepartments(): HasMany
    {
        return $this->hasMany(DepartmentModel::class, 'parent_department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(EmployeeModel::class, 'department_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(PositionModel::class, 'department_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithEmployeeCount($query)
    {
        return $query->withCount('employees');
    }

    public function scopeWithActiveEmployees($query)
    {
        return $query->with(['employees' => function ($query) {
            $query->where('employment_status', 'active');
        }]);
    }
}