<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpiryCheck extends Model
{
    protected $table = 'sa_expiry_checks';

    protected $fillable = [
        'sa_company_id', 'sa_audit_id', 'title', 'check_date', 'notes',
        'total_system_value', 'total_physical_value', 'total_missing_value',
    ];

    protected function casts(): array
    {
        return [
            'check_date' => 'date',
            'total_system_value' => 'decimal:2',
            'total_physical_value' => 'decimal:2',
            'total_missing_value' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'sa_audit_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpiryCheckItem::class, 'sa_expiry_check_id');
    }
}
