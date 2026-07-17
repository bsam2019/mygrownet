<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Extension extends Model
{
    protected $table = 'extensions';

    protected $fillable = [
        'code',
        'name',
        'description',
        'version',
        'provider_class',
        'default_settings',
        'is_active',
        'trial_days',
        'price_monthly_usd',
        'price_yearly_usd',
        'price_monthly_zmw',
        'price_yearly_zmw',
    ];

    protected $casts = [
        'default_settings' => 'array',
        'is_active' => 'boolean',
        'trial_days' => 'integer',
        'price_monthly_usd' => 'decimal:2',
        'price_yearly_usd' => 'decimal:2',
        'price_monthly_zmw' => 'decimal:2',
        'price_yearly_zmw' => 'decimal:2',
    ];

    public function companySubscriptions(): HasMany
    {
        return $this->hasMany(CompanyExtension::class, 'extension_id');
    }
}
