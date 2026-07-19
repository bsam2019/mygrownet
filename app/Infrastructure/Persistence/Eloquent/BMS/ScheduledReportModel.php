<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduledReportModel extends Model
{
    protected $table = 'cms_scheduled_reports';

    protected $fillable = [
        'company_id',
        'name',
        'report_type',
        'frequency',
        'day_of_week',
        'day_of_month',
        'time_of_day',
        'recipients',
        'format',
        'is_active',
        'last_sent_at',
        'next_run_at',
        'created_by',
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
        'next_run_at' => 'datetime',
        'time_of_day' => 'datetime:H:i:s',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ScheduledReportLogModel::class, 'scheduled_report_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueForExecution($query)
    {
        return $query->active()
            ->where('next_run_at', '<=', now());
    }
}
