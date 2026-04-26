<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteDiaryEntryModel extends Model
{
    protected $table = 'cms_site_diary_entries';

    protected $fillable = [
        'project_id',
        'created_by',
        'entry_date',
        'start_time',
        'end_time',
        'weather',
        'workers_on_site',
        'work_completed',
        'materials_delivered',
        'equipment_used',
        'issues_delays',
        'safety_incidents',
        'visitors',
        'photos',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'workers_on_site' => 'integer',
        'photos' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'created_by');
    }
}
