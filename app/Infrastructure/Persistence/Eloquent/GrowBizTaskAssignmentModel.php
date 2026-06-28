<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizTaskAssignmentModel extends Model
{
    protected $table = 'growbiz_task_assignments';

    protected $fillable = [
        'task_id',
        'employee_id',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'employee_id' => 'integer',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(GrowBizTaskModel::class, 'task_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(GrowBizEmployeeModel::class, 'employee_id');
    }
}
