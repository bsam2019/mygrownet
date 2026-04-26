<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspectionResultModel extends Model
{
    protected $table = 'cms_quality_inspection_results';

    protected $fillable = [
        'inspection_id',
        'checklist_item_id',
        'result',
        'measurement',
        'notes',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(QualityInspectionModel::class, 'inspection_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(QualityChecklistItemModel::class, 'checklist_item_id');
    }
}
