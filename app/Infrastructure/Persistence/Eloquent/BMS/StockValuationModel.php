<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockValuationModel extends Model
{
    protected $table = 'cms_stock_valuation';

    protected $fillable = [
        'company_id',
        'material_id',
        'location_id',
        'valuation_date',
        'quantity',
        'unit_cost',
        'total_value',
        'valuation_method',
    ];

    protected $casts = [
        'valuation_date' => 'date',
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocationModel::class, 'location_id');
    }
}
