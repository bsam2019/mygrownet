<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingTemplateModel extends Model
{
    protected $table = 'cms_onboarding_templates';

    protected $fillable = [
        'company_id',
        'template_name',
        'department_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(OnboardingTaskModel::class, 'template_id')->orderBy('display_order');
    }

    public function employeeOnboardings(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingModel::class, 'template_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
