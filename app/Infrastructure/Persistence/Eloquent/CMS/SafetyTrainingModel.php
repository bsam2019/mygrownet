<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyTrainingModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_safety_training';

    protected $fillable = [
        'company_id',
        'training_name',
        'training_type',
        'description',
        'duration_hours',
        'validity_period_months',
        'is_mandatory',
    ];

    protected $casts = [
        'duration_hours' => 'decimal:2',
        'validity_period_months' => 'integer',
        'is_mandatory' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(TrainingRecordModel::class, 'training_id');
    }
}
