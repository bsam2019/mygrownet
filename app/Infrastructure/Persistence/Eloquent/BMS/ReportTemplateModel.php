<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTemplateModel extends Model
{
    protected $table = 'cms_report_templates';

    protected $fillable = [
        'company_id',
        'name',
        'category',
        'description',
        'parameters',
        'columns',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'columns' => 'array',
        'is_system' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function savedReports(): HasMany
    {
        return $this->hasMany(SavedReportModel::class, 'template_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReportScheduleModel::class, 'template_id');
    }
}
