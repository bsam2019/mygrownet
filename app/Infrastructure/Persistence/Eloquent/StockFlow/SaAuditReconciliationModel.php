<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaAuditReconciliationModel extends Model
{
    protected $table = 'sa_audit_reconciliations';
    protected $fillable = [
        'sa_audit_id', 'sa_department_id', 'sa_bin_id',
        'system_value', 'physical_value', 'variance', 'variance_percent',
    ];
    protected $casts = [
        'system_value' => 'decimal:2',
        'physical_value' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_percent' => 'decimal:2',
    ];

    public function audit(): BelongsTo { return $this->belongsTo(SaAuditModel::class, 'sa_audit_id'); }
    public function department(): BelongsTo { return $this->belongsTo(SaDepartmentModel::class, 'sa_department_id'); }
    public function bin(): BelongsTo { return $this->belongsTo(SaBinModel::class, 'sa_bin_id'); }
}
