<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceWorkflowTemplateModel extends Model
{
    protected $table = 'growfinance_workflow_templates';

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'entity_type',
        'steps',
        'is_active',
        'sla_hours',
        'allow_escalation',
    ];

    protected $casts = [
        'steps' => 'array',
        'is_active' => 'boolean',
        'sla_hours' => 'integer',
        'allow_escalation' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfEntityType($query, string $type)
    {
        return $query->where('entity_type', $type);
    }
}
