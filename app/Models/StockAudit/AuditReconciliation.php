<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditReconciliation extends Model
{
    protected $table = 'sa_audit_reconciliations';

    protected $fillable = [
        'sa_audit_id', 'sa_department_id', 'sa_bin_id',
        'system_value', 'physical_value', 'variance', 'variance_percent',
    ];

    protected function casts(): array
    {
        return [
            'system_value' => 'decimal:2',
            'physical_value' => 'decimal:2',
            'variance' => 'decimal:2',
            'variance_percent' => 'decimal:2',
        ];
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'sa_audit_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'sa_department_id');
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'sa_bin_id');
    }
}
