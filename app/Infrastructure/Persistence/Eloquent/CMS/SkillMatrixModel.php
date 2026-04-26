<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillMatrixModel extends Model
{
    protected $table = 'cms_skill_matrix';

    protected $fillable = [
        'employee_id', 'skill_name', 'proficiency_level', 'acquired_date',
        'last_used_date', 'certification_number', 'certification_expiry', 'notes',
    ];

    protected $casts = [
        'acquired_date' => 'date',
        'last_used_date' => 'date',
        'certification_expiry' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }

    public function isCertificationExpired(): bool
    {
        return $this->certification_expiry && now()->gt($this->certification_expiry);
    }
}
