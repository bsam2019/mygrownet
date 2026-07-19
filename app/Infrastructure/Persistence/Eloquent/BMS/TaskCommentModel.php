<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskCommentModel extends Model
{
    protected $table = 'cms_task_comments';

    protected $fillable = [
        'task_id',
        'user_id',
        'parent_id',
        'comment',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TaskCommentModel::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TaskCommentModel::class, 'parent_id');
    }
}
