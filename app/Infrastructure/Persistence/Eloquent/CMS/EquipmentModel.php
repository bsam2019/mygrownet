<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_equipment';

    protected $fillable = [
        'company_id', 'equipment_code', 'name', 'category', 'description',
        'manufacturer', 'model', 'serial_number', 'purchase_date', 'purchase_cost',
        'current_value', 'ownership', 'status', 'location', 'useful_life_years',
        'last_maintenance_date', 'next_maintenance_date', 'maintenance_interval_days', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'useful_life_years' => 'integer',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'maintenance_interval_days' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(EquipmentMaintenanceModel::class, 'equipment_id');
    }

    public function usageRecords(): HasMany
    {
        return $this->hasMany(EquipmentUsageModel::class, 'equipment_id');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(EquipmentRentalModel::class, 'equipment_id');
    }

    public function isMaintenanceDue(): bool
    {
        return $this->next_maintenance_date && now()->gte($this->next_maintenance_date);
    }

    public function calculateDepreciation(): float
    {
        if (!$this->purchase_cost || !$this->purchase_date || !$this->useful_life_years) {
            return 0;
        }

        $yearsOwned = now()->diffInYears($this->purchase_date);
        $annualDepreciation = $this->purchase_cost / $this->useful_life_years;
        $totalDepreciation = $annualDepreciation * $yearsOwned;

        return min($totalDepreciation, $this->purchase_cost);
    }
}
