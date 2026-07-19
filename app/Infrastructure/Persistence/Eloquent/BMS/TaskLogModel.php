<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLogModel extends Model
{
    protected $table = 'cms_task_logs';

    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'note',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
