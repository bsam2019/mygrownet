<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskResourceModel extends Model
{
    protected $table = 'cms_task_resources';

    protected $fillable = [
        'company_id',
        'task_id',
        'resource_type',
        'resource_id',
        'allocated_from',
        'allocated_to',
        'status',
        'notes',
    ];

    protected $casts = [
        'allocated_from' => 'datetime',
        'allocated_to' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    // Polymorphic relationship to resource
    public function resource()
    {
        return $this->morphTo('resource', 'resource_type', 'resource_id');
    }
}
