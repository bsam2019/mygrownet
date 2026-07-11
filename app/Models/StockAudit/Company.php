<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $table = 'sa_companies';

    protected $fillable = [
        'name', 'subdomain', 'address', 'city', 'country', 'phone', 'email',
        'contact_person', 'currency', 'logo_path', 'status', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CompanySubscription::class, 'id', 'sa_company_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'sa_company_id');
    }

    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class, 'sa_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'sa_company_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'sa_company_id');
    }

    public function physicalCounts(): HasMany
    {
        return $this->hasMany(PhysicalCount::class, 'sa_company_id');
    }

    public function expiryChecks(): HasMany
    {
        return $this->hasMany(ExpiryCheck::class, 'sa_company_id');
    }
}
