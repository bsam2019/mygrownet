<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyIncidentModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_safety_incidents';

    protected $fillable = [
        'company_id',
        'incident_number',
        'incident_date',
        'incident_time',
        'location',
        'incident_type',
        'severity',
        'description',
        'immediate_action',
        'reported_by',
        'investigated_by',
        'investigation_date',
        'root_cause',
        'corrective_actions',
        'status',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'investigation_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function investigatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    public function involvedPersons(): HasMany
    {
        return $this->hasMany(IncidentInvolvedPersonModel::class, 'incident_id');
    }
}
