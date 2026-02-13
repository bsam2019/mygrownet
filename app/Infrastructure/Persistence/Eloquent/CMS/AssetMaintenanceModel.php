<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenanceModel extends Model
{
    protected $table = 'cms_asset_maintenance';

    protected $fillable = [
        'company_id',
        'asset_id',
        'maintenance_type',
        'description',
        'scheduled_date',
        'completed_date',
        'cost',
        'performed_by',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    // Relationships
    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetModel::class, 'asset_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    // Scopes
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('status', 'scheduled')
                     ->whereBetween('scheduled_date', [now(), now()->addDays($days)]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_date', '<', now());
    }
}
