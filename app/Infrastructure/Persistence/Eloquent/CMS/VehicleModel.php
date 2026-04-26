<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_vehicles';

    protected $fillable = [
        'company_id',
        'registration_number',
        'make',
        'model',
        'year',
        'vehicle_type',
        'fuel_type',
        'purchase_date',
        'purchase_cost',
        'current_mileage',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_mileage' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function currentDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_driver_id');
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecordModel::class, 'vehicle_id');
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(VehicleMaintenanceModel::class, 'vehicle_id');
    }

    public function tripLogs(): HasMany
    {
        return $this->hasMany(TripLogModel::class, 'vehicle_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(VehicleExpenseModel::class, 'vehicle_id');
    }
}
