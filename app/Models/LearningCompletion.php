<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningCompletion extends Model
{
    protected $fillable = [
        'user_id',
        'learning_module_id',
        'started_at',
        'completed_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class, 'learning_module_id');
    }
}
