<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Audit extends Model
{
    protected $table = 'sa_audits';

    protected $fillable = [
        'sa_company_id', 'title', 'report_reference', 'audit_date',
        'prepared_for', 'prepared_by', 'status',
        'total_system_value', 'total_physical_value', 'total_recorded_sales',
        'total_variance', 'unaccounted_value',
        'executive_summary', 'recommendations', 'conclusion', 'attachments',
    ];

    protected function casts(): array
    {
        return [
            'audit_date' => 'date',
            'total_system_value' => 'decimal:2',
            'total_physical_value' => 'decimal:2',
            'total_recorded_sales' => 'decimal:2',
            'total_variance' => 'decimal:2',
            'unaccounted_value' => 'decimal:2',
            'attachments' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(AuditReconciliation::class, 'sa_audit_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AuditItem::class, 'sa_audit_id');
    }
}
