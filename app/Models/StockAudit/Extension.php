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
    ];

    protected $casts = [
        'default_settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function companySubscriptions(): HasMany
    {
        return $this->hasMany(CompanyExtension::class, 'extension_id');
    }
}
