<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAvailabilityModel extends Model
{
    protected $table = 'cms_resource_availability';

    protected $fillable = [
        'company_id',
        'resource_type',
        'resource_id',
        'date',
        'available_from',
        'available_to',
        'is_available',
        'unavailability_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }
}
