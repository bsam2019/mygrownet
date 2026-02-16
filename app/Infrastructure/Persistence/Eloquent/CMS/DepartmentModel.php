<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentModel extends Model
{
    protected $table = 'cms_departments';

    protected $fillable = [
        'company_id',
        'branch_id',
        'department_code',
        'department_name',
        'description',
        'manager_id',
        'parent_department_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'manager_id');
    }

    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'parent_department_id');
    }

    public function subDepartments(): HasMany
    {
        return $this->hasMany(DepartmentModel::class, 'parent_department_id');
    }

    public function workers(): HasMany
    {
        return $this->hasMany(WorkerModel::class, 'department_id');
    }
}
