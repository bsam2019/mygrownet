<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRentalModel extends Model
{
    protected $table = 'cms_equipment_rentals';

    protected $fillable = [
        'equipment_id', 'project_id', 'rental_company', 'rental_agreement_number',
        'rental_start_date', 'rental_end_date', 'daily_rate', 'total_cost',
        'deposit', 'status', 'notes',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'daily_rate' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'deposit' => 'decimal:2',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }
}
