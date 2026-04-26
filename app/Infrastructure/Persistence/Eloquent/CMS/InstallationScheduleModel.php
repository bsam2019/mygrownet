<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallationScheduleModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_installation_schedules';

    protected $fillable = [
        'company_id',
        'job_id',
        'project_id',
        'schedule_number',
        'scheduled_date',
        'start_time',
        'end_time',
        'status',
        'team_leader_id',
        'site_address',
        'site_contact_name',
        'site_contact_phone',
        'special_instructions',
        'equipment_required',
        'materials_required',
        'estimated_hours',
        'actual_hours',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function teamLeader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'team_leader_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(InstallationTeamMemberModel::class, 'installation_schedule_id');
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisitModel::class, 'installation_schedule_id');
    }
}
