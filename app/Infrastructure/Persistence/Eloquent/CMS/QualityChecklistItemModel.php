<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityChecklistItemModel extends Model
{
    protected $table = 'cms_quality_checklist_items';

    protected $fillable = [
        'checklist_id',
        'item_description',
        'acceptance_criteria',
        'sort_order',
        'is_critical',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_critical' => 'boolean',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(QualityChecklistModel::class, 'checklist_id');
    }
}
