<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignModel extends Model
{
    protected $table = 'cms_campaigns';

    protected $fillable = [
        'company_id', 'name', 'description', 'type', 'status',
        'segment_id', 'target_customer_ids', 'start_date', 'end_date',
        'budget', 'target_leads', 'target_revenue',
        'sent_count', 'opened_count', 'clicked_count', 'converted_count', 'actual_revenue',
        'created_by',
    ];

    protected $casts = [
        'target_customer_ids' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
        'target_revenue' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(CustomerSegmentModel::class, 'segment_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
