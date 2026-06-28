<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrewModel extends Model
{
    protected $table = 'cms_crews';

    protected $fillable = [
        'company_id', 'crew_name', 'foreman_id', 'specialization', 'status', 'notes',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function foreman(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'foreman_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CrewMemberModel::class, 'crew_id');
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(LabourTimesheetModel::class, 'crew_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->where('status', 'active');
    }
}
