<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerSkillModel extends Model
{
    protected $table = 'cms_worker_skills';

    protected $fillable = [
        'worker_id',
        'skill_id',
        'proficiency_level',
        'acquired_date',
        'last_assessed_date',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'acquired_date' => 'date',
        'last_assessed_date' => 'date',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(SkillModel::class, 'skill_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'verified_by');
    }
}
