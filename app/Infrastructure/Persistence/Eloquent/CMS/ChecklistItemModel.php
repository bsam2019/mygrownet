<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItemModel extends Model
{
    protected $table = 'cms_checklist_items';

    protected $fillable = [
        'checklist_id',
        'item_description',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_required' => 'boolean',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(InstallationChecklistModel::class, 'checklist_id');
    }
}
