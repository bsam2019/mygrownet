<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportScheduleModel extends Model
{
    protected $table = 'cms_report_schedules';

    protected $fillable = [
        'template_id',
        'created_by',
        'schedule_name',
        'frequency',
        'recipients',
        'filters',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'filters' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplateModel::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }
}
