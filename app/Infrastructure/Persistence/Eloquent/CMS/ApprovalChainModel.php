<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalChainModel extends Model
{
    protected $table = 'cms_approval_chains';

    protected $fillable = [
        'company_id',
        'name',
        'entity_type',
        'min_amount',
        'max_amount',
        'approval_steps',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'approval_steps' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    public function matchesAmount(float $amount): bool
    {
        if ($amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount !== null && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }
}
