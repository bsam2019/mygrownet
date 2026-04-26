<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentInvolvedPersonModel extends Model
{
    protected $table = 'cms_incident_involved_persons';

    protected $fillable = [
        'incident_id',
        'person_name',
        'person_type',
        'injury_type',
        'injury_description',
        'medical_treatment',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(SafetyIncidentModel::class, 'incident_id');
    }
}
