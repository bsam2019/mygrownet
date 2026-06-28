<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignmentModel extends Model
{
    protected $table = 'cms_asset_assignments';

    protected $fillable = [
        'company_id',
        'asset_id',
        'assigned_to',
        'assigned_date',
        'returned_date',
        'condition_on_assignment',
        'condition_on_return',
        'assignment_notes',
        'return_notes',
        'assigned_by',
        'returned_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'returned_date' => 'date',
    ];

    // Relationships
    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetModel::class, 'asset_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'assigned_to');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'assigned_by');
    }

    public function returnedByUser(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'returned_by');
    }

    // Scopes
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('returned_date');
    }

    public function scopeReturned($query)
    {
        return $query->whereNotNull('returned_date');
    }
}
