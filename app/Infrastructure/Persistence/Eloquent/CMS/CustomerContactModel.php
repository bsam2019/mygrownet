<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContactModel extends Model
{
    protected $table = 'cms_customer_contacts';

    protected $fillable = [
        'company_id',
        'customer_id',
        'name',
        'title',
        'email',
        'phone',
        'mobile',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
