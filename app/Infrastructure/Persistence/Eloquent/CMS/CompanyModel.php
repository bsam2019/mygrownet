<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyModel extends Model
{
    protected $table = 'cms_companies';

    protected $fillable = [
        'name',
        'industry_type',
        'business_registration_number',
        'tax_number',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'logo_path',
        'invoice_footer',
        'status',
        'settings',
        'onboarding_completed',
        'onboarding_progress',
        'onboarding_completed_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'onboarding_completed' => 'boolean',
        'onboarding_progress' => 'array',
        'onboarding_completed_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(CmsUserModel::class, 'company_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(RoleModel::class, 'company_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(CustomerModel::class, 'company_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(JobModel::class, 'company_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}
