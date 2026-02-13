<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FollowUpModel extends Model
{
    protected $table = 'cms_follow_ups';

    protected $fillable = [
        'company_id', 'followable_type', 'followable_id',
        'title', 'description', 'due_date', 'priority', 'status',
        'assigned_to', 'completed_at', 'completion_notes', 'created_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function followable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')->where('due_date', '<', now());
    }
}
