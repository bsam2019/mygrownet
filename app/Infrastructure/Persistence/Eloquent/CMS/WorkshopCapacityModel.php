<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopCapacityModel extends Model
{
    protected $table = 'cms_workshop_capacity';

    protected $fillable = [
        'company_id',
        'date',
        'available_workers',
        'available_hours',
        'scheduled_hours',
        'actual_hours',
        'utilization_percentage',
        'is_working_day',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'available_hours' => 'decimal:2',
        'scheduled_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'utilization_percentage' => 'decimal:2',
        'is_working_day' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }
}
