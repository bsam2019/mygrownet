<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistTemplateItemModel extends Model
{
    protected $table = 'cms_checklist_template_items';

    protected $fillable = [
        'template_id',
        'label',
        'description',
        'is_required',
        'sequence_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sequence_order' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskChecklistTemplateModel::class, 'template_id');
    }
}
