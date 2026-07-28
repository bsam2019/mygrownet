<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceDepreciationScheduleModel extends Model
{
    protected $table = 'growfinance_depreciation_schedule';
    protected $guarded = ['id'];

    protected $casts = [
        'period_date' => 'date',
        'depreciation_amount' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'net_book_value' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(GrowFinanceFixedAssetModel::class, 'asset_id');
    }
}
