<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class JobModel extends Model
{
    protected $table = 'cms_jobs';

    protected $fillable = [
        'company_id',
        'customer_id',
        'job_number',
        'job_type',
        'description',
        'quoted_value',
        'actual_value',
        'material_cost',
        'labor_cost',
        'overhead_cost',
        'total_cost',
        'profit_amount',
        'profit_margin',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'started_at',
        'completed_at',
        'deadline',
        'notes',
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'quoted_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'material_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'deadline' => 'datetime',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'locked_by');
    }

    public function invoice()
    {
        return $this->hasOne(InvoiceModel::class, 'job_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(JobAttachmentModel::class, 'job_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(JobStatusHistoryModel::class, 'job_id')->orderBy('created_at', 'desc');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast() && !$this->isCompleted();
    }

    public function calculateProfit(): void
    {
        $this->total_cost = $this->material_cost + $this->labor_cost + $this->overhead_cost;
        $this->profit_amount = ($this->actual_value ?? 0) - $this->total_cost;
        
        if ($this->actual_value > 0) {
            $this->profit_margin = ($this->profit_amount / $this->actual_value) * 100;
        } else {
            $this->profit_margin = 0;
        }
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('deadline', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }
}
