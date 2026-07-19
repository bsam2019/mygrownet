<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceAccountMappingModel extends Model
{
    protected $table = 'cms_growfinance_account_mappings';

    protected $fillable = [
        'company_id',
        'cms_entity_type',
        'cms_category',
        'cms_payment_method',
        'growfinance_account_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'company_id');
    }

    public function growfinanceAccount(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceAccountModel::class, 'growfinance_account_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForEntityType($query, string $entityType)
    {
        return $query->where('cms_entity_type', $entityType);
    }

    public function scopeForCategory($query, ?string $category)
    {
        return $query->where('cms_category', $category);
    }

    public function scopeForPaymentMethod($query, ?string $paymentMethod)
    {
        return $query->where('cms_payment_method', $paymentMethod);
    }

    public function scopeDefaults($query)
    {
        return $query->where('is_default', true);
    }
}
