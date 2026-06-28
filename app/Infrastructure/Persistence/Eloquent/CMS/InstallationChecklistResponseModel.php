<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationChecklistResponseModel extends Model
{
    protected $table = 'cms_installation_checklist_responses';

    protected $fillable = [
        'site_visit_id',
        'checklist_id',
        'checklist_item_id',
        'response',
        'notes',
        'checked_by',
    ];

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisitModel::class, 'site_visit_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(InstallationChecklistModel::class, 'checklist_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItemModel::class, 'checklist_item_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
