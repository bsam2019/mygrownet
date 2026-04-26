<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteVisitModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_site_visits';

    protected $fillable = [
        'installation_schedule_id',
        'visit_date',
        'visit_type',
        'arrival_time',
        'departure_time',
        'work_performed',
        'issues_encountered',
        'next_steps',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(InstallationScheduleModel::class, 'installation_schedule_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InstallationPhotoModel::class, 'site_visit_id');
    }

    public function checklistResponses(): HasMany
    {
        return $this->hasMany(InstallationChecklistResponseModel::class, 'site_visit_id');
    }

    public function signoff(): HasMany
    {
        return $this->hasMany(CustomerSignoffModel::class, 'site_visit_id');
    }
}
