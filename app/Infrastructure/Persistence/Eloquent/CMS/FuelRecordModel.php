<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRecordModel extends Model
{
    protected $table = 'cms_fuel_records';

    protected $fillable = [
        'vehicle_id',
        'fuel_date',
        'quantity',
        'unit_price',
        'total_cost',
        'mileage',
        'filled_by',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'fuel_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'mileage' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id');
    }
}
