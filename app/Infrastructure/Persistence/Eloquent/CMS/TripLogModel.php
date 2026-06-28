<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripLogModel extends Model
{
    protected $table = 'cms_trip_logs';

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'trip_date',
        'start_location',
        'end_location',
        'start_mileage',
        'end_mileage',
        'distance',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'start_mileage' => 'decimal:2',
        'end_mileage' => 'decimal:2',
        'distance' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
