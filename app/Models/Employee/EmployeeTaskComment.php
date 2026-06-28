<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'employee_id',
        'content',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(EmployeeTask::class, 'task_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
