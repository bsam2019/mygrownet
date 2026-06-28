<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LeadModel extends Model
{
    protected $table = 'cms_leads';

    protected $fillable = [
        'company_id', 'lead_number', 'name', 'email', 'phone', 'company_name', 'job_title',
        'status', 'source', 'priority', 'estimated_value', 'probability',
        'assigned_to', 'industry', 'company_size', 'tags',
        'notes', 'last_contacted_at', 'next_follow_up', 'converted_at', 'converted_to_customer_id',
        'created_by',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'tags' => 'array',
        'last_contacted_at' => 'datetime',
        'next_follow_up' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function convertedToCustomer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'converted_to_customer_id');
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(CommunicationModel::class, 'communicable');
    }

    public function followUps(): MorphMany
    {
        return $this->morphMany(FollowUpModel::class, 'followable');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
