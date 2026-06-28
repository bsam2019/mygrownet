<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OpportunityModel extends Model
{
    protected $table = 'cms_opportunities';

    protected $fillable = [
        'company_id', 'opportunity_number', 'customer_id', 'lead_id',
        'name', 'description', 'amount', 'probability', 'weighted_amount',
        'stage', 'priority', 'expected_close_date', 'actual_close_date',
        'assigned_to', 'notes', 'loss_reason', 'last_activity_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'weighted_amount' => 'decimal:2',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'last_activity_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(LeadModel::class, 'lead_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
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

    public function scopeByStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }
}
