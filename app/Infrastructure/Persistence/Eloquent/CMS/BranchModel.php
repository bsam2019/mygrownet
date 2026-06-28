<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchModel extends Model
{
    protected $table = 'cms_branches';

    protected $fillable = [
        'company_id',
        'branch_code',
        'branch_name',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'is_head_office',
        'is_active',
        'manager_id',
    ];

    protected $casts = [
        'is_head_office' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'manager_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(DepartmentModel::class, 'branch_id');
    }
}
