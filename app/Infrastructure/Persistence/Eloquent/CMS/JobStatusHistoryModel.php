<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobStatusHistoryModel extends Model
{
    protected $table = 'cms_job_status_history';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'job_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'changed_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForJob($query, int $jobId)
    {
        return $query->where('job_id', $jobId);
    }
}
