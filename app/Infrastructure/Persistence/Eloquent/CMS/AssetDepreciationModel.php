<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciationModel extends Model
{
    protected $table = 'cms_asset_depreciation';

    protected $fillable = [
        'company_id',
        'asset_id',
        'method',
        'useful_life_years',
        'salvage_value',
        'annual_depreciation',
        'depreciation_start_date',
    ];

    protected $casts = [
        'salvage_value' => 'decimal:2',
        'annual_depreciation' => 'decimal:2',
        'depreciation_start_date' => 'date',
    ];

    // Relationships
    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetModel::class, 'asset_id');
    }

    // Scopes
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
