<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizTaskCommentModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_task_comments';

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(GrowBizTaskModel::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
