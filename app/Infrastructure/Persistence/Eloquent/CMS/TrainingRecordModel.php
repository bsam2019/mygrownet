<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingRecordModel extends Model
{
    protected $table = 'cms_training_records';

    protected $fillable = [
        'training_id',
        'user_id',
        'training_date',
        'trainer_name',
        'score',
        'status',
        'certificate_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'training_date' => 'date',
        'score' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(SafetyTrainingModel::class, 'training_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
