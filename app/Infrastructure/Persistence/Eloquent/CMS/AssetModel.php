<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetModel extends Model
{
    protected $table = 'cms_assets';

    protected $fillable = [
        'company_id',
        'asset_number',
        'name',
        'description',
        'category',
        'serial_number',
        'model',
        'manufacturer',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'condition',
        'status',
        'location',
        'assigned_to',
        'assigned_date',
        'warranty_months',
        'warranty_expiry',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'assigned_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
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

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignmentModel::class, 'asset_id');
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(AssetMaintenanceModel::class, 'asset_id');
    }

    public function depreciation(): HasMany
    {
        return $this->hasMany(AssetDepreciationModel::class, 'asset_id');
    }

    // Scopes
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->whereHas('maintenanceRecords', function ($q) {
            $q->where('status', 'scheduled')
              ->where('scheduled_date', '<=', now()->addDays(7));
        });
    }
}
