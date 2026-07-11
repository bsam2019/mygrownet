<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaAuditModel extends Model
{
    protected $table = 'sa_audits';
    protected $fillable = [
        'sa_company_id', 'title', 'report_reference', 'audit_date',
        'status', 'total_system_value', 'total_physical_value',
        'total_variance', 'unaccounted_value', 'total_recorded_sales',
        'executive_summary', 'recommendations', 'conclusion',
        'prepared_for', 'prepared_by',
    ];
    protected $casts = [
        'audit_date' => 'date',
        'total_system_value' => 'decimal:2',
        'total_physical_value' => 'decimal:2',
        'total_variance' => 'decimal:2',
        'unaccounted_value' => 'decimal:2',
        'total_recorded_sales' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function items(): HasMany { return $this->hasMany(SaAuditItemModel::class, 'sa_audit_id'); }
    public function reconciliations(): HasMany { return $this->hasMany(SaAuditReconciliationModel::class, 'sa_audit_id'); }
}
