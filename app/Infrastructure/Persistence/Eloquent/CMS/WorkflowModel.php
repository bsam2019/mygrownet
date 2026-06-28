<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowModel extends Model
{
    protected $table = 'cms_workflows';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'workflow_type',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStageModel::class, 'workflow_id')->orderBy('sequence_order');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'workflow_id');
    }

    public function bottlenecks(): HasMany
    {
        return $this->hasMany(WorkflowBottleneckModel::class, 'workflow_id');
    }
}
