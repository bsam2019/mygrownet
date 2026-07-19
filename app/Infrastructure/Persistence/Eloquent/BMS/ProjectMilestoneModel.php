<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestoneModel extends Model
{
    protected $table = 'cms_project_milestones';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'target_date',
        'actual_date',
        'status',
        'order',
        'payment_percentage',
    ];

    protected $casts = [
        'target_date' => 'date',
        'actual_date' => 'date',
        'payment_percentage' => 'decimal:2',
        'order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function isDelayed(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }
        return now()->gt($this->target_date);
    }
}
