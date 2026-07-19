<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentMaintenanceModel extends Model
{
    protected $table = 'cms_equipment_maintenance';

    protected $fillable = [
        'equipment_id', 'performed_by', 'maintenance_date', 'type', 'description',
        'cost', 'downtime_hours', 'service_provider', 'parts_replaced', 'next_maintenance_date',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'downtime_hours' => 'integer',
        'next_maintenance_date' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'performed_by');
    }
}
