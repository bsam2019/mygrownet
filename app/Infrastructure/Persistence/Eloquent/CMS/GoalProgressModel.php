<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalProgressModel extends Model
{
    protected $table = 'cms_goal_progress';

    protected $fillable = [
        'goal_id',
        'updated_by_user_id',
        'progress_percentage',
        'update_notes',
        'update_date',
    ];

    protected $casts = [
        'progress_percentage' => 'integer',
        'update_date' => 'date',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(GoalModel::class, 'goal_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'updated_by_user_id');
    }
}
