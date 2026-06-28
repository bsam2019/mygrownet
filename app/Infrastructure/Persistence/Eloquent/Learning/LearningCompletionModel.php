<?php

namespace App\Infrastructure\Persistence\Eloquent\Learning;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningCompletionModel extends Model
{
    protected $table = 'learning_completions';

    protected $fillable = [
        'user_id',
        'learning_module_id',
        'started_at',
        'completed_at',
        'time_spent_seconds',
        'current_page',
        'last_accessed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'current_page' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(LearningModuleModel::class, 'learning_module_id');
    }

    public function scopeCompletedToday($query)
    {
        return $query->whereDate('completed_at', today());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
