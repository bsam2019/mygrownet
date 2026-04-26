<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteTrackingModel extends Model
{
    protected $table = 'cms_waste_tracking';

    protected $fillable = [
        'company_id',
        'production_order_id',
        'material_id',
        'waste_date',
        'waste_type',
        'quantity',
        'unit',
        'value',
        'disposal_method',
        'reason',
        'recorded_by',
    ];

    protected $casts = [
        'waste_date' => 'date',
        'quantity' => 'decimal:2',
        'value' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderModel::class, 'production_order_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }
}
