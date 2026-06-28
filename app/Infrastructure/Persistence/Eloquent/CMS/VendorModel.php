<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorModel extends Model
{
    protected $table = 'cms_vendors';

    protected $fillable = [
        'company_id', 'vendor_number', 'name', 'email', 'phone', 'contact_person',
        'address', 'city', 'country', 'tax_number', 'registration_number', 'vendor_type',
        'payment_terms_days', 'payment_method', 'bank_name', 'bank_account_number', 'mobile_money_number',
        'status', 'notes', 'total_spent', 'total_orders', 'average_rating', 'created_by',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'average_rating' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrderModel::class, 'vendor_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(VendorRatingModel::class, 'vendor_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
