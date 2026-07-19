<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DefectModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_defects';

    protected $fillable = [
        'company_id',
        'installation_schedule_id',
        'job_id',
        'defect_number',
        'title',
        'description',
        'severity',
        'location',
        'reported_by',
        'reported_date',
        'assigned_to',
        'target_resolution_date',
        'status',
        'resolved_by',
        'resolved_date',
        'resolution_notes',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'target_resolution_date' => 'date',
        'resolved_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(InstallationScheduleModel::class, 'installation_schedule_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DefectPhotoModel::class, 'defect_id');
    }
}
