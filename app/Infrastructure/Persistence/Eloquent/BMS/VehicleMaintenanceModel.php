<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMaintenanceModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_vehicle_maintenance';

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'scheduled_date',
        'completed_date',
        'description',
        'performed_by',
        'estimated_cost',
        'actual_cost',
        'mileage',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'mileage' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id');
    }
}
