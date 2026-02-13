<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAuditLogModel extends Model
{
    protected $table = 'cms_security_audit_log';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'company_id',
        'event_type',
        'ip_address',
        'user_agent',
        'description',
        'metadata',
        'severity',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }
}
