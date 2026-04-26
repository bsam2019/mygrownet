<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SiteVisitModel extends Model
{
    protected $table = 'cms_site_visits';

    protected $fillable = [
        'company_id',
        'installation_schedule_id',
        'job_id',
        'visit_number',
        'visit_date',
        'arrival_time',
        'departure_time',
        'visit_type',
        'status',
        'visited_by',
        'purpose',
        'findings',
        'work_performed',
        'issues_encountered',
        'next_steps',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function installationSchedule(): BelongsTo
    {
        return $this->belongsTo(InstallationScheduleModel::class, 'installation_schedule_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function visitedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'visited_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InstallationPhotoModel::class, 'site_visit_id');
    }

    public function checklistResponses(): HasMany
    {
        return $this->hasMany(InstallationChecklistResponseModel::class, 'site_visit_id');
    }

    public function signoff(): HasOne
    {
        return $this->hasOne(CustomerSignoffModel::class, 'site_visit_id');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(DefectModel::class, 'site_visit_id');
    }
}
