<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyExtension extends Model
{
    protected $table = 'company_extensions';

    protected $fillable = [
        'sa_company_id',
        'extension_id',
        'status',
        'settings',
        'subscribed_at',
        'expires_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'subscribed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class, 'extension_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'trial';
    }
}
