<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedReportModel extends Model
{
    protected $table = 'cms_saved_reports';

    protected $fillable = [
        'template_id',
        'generated_by',
        'report_name',
        'filters_used',
        'date_from',
        'date_to',
        'file_path',
        'file_format',
    ];

    protected $casts = [
        'filters_used' => 'array',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplateModel::class, 'template_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'generated_by');
    }
}
