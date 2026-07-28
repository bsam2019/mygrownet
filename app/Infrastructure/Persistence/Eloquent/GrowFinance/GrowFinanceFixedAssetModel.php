<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use App\Domain\GrowFinance\ValueObjects\AssetStatus;
use App\Domain\GrowFinance\ValueObjects\DepreciationMethod;
use Illuminate\Database\Eloquent\Model;

class GrowFinanceFixedAssetModel extends Model
{
    protected $table = 'growfinance_fixed_assets';
    protected $guarded = ['id'];

    protected $casts = [
        'purchase_date' => 'date',
        'cost' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'disposal_proceeds' => 'decimal:2',
        'disposal_date' => 'date',
        'status' => AssetStatus::class,
        'depreciation_method' => DepreciationMethod::class,
    ];

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function depreciationSchedule()
    {
        return $this->hasMany(GrowFinanceDepreciationScheduleModel::class, 'asset_id');
    }
}
