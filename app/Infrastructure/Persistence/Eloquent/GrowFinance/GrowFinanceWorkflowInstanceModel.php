<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceWorkflowInstanceModel extends Model
{
    protected $table = 'growfinance_workflow_instances';

    protected $fillable = [
        'business_id',
        'workflow_template_id',
        'entity_type',
        'entity_id',
        'status',
        'current_step',
        'approval_log',
        'requested_by',
        'completed_at',
    ];

    protected $casts = [
        'approval_log' => 'array',
        'current_step' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceWorkflowTemplateModel::class, 'workflow_template_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeOfEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)->where('entity_id', $entityId);
    }

    public function scopeNeedsAttention($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress', 'escalated']);
    }
}
