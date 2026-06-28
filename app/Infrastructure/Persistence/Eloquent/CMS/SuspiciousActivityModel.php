<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuspiciousActivityModel extends Model
{
    protected $table = 'cms_suspicious_activities';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'company_id',
        'activity_type',
        'ip_address',
        'description',
        'details',
        'status',
        'detected_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'details' => 'array',
        'detected_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
