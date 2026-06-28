<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRulesModel extends Model
{
    protected $table = 'cms_pricing_rules';

    protected $fillable = [
        'company_id',
        'sliding_window_rate',
        'casement_window_rate',
        'sliding_door_rate',
        'hinged_door_rate',
        'other_rate',
        'material_cost_per_m2',
        'labour_cost_per_m2',
        'overhead_cost_per_m2',
        'minimum_profit_percent',
        'tax_rate',
    ];

    protected $casts = [
        'sliding_window_rate' => 'decimal:2',
        'casement_window_rate' => 'decimal:2',
        'sliding_door_rate' => 'decimal:2',
        'hinged_door_rate' => 'decimal:2',
        'other_rate' => 'decimal:2',
        'material_cost_per_m2' => 'decimal:2',
        'labour_cost_per_m2' => 'decimal:2',
        'overhead_cost_per_m2' => 'decimal:2',
        'minimum_profit_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    /**
     * Get selling rate for a specific type
     */
    public function getRateForType(string $type): float
    {
        return match($type) {
            'sliding_window' => (float)$this->sliding_window_rate,
            'casement_window' => (float)$this->casement_window_rate,
            'sliding_door' => (float)$this->sliding_door_rate,
            'hinged_door' => (float)$this->hinged_door_rate,
            'other' => (float)$this->other_rate,
            default => 0.0,
        };
    }

    /**
     * Get total cost per m² (material + labour + overhead)
     */
    public function getTotalCostPerM2(): float
    {
        return (float)$this->material_cost_per_m2 
            + (float)$this->labour_cost_per_m2 
            + (float)$this->overhead_cost_per_m2;
    }

    /**
     * Calculate selling price for an item
     */
    public function calculateSellingPrice(string $type, float $area): float
    {
        $rate = $this->getRateForType($type);
        return $rate * $area;
    }

    /**
     * Calculate cost for an item
     */
    public function calculateCost(float $area): float
    {
        return $this->getTotalCostPerM2() * $area;
    }

    /**
     * Calculate profit for an item
     */
    public function calculateProfit(string $type, float $area): array
    {
        $sellingPrice = $this->calculateSellingPrice($type, $area);
        $cost = $this->calculateCost($area);
        $profit = $sellingPrice - $cost;
        $profitPercent = $sellingPrice > 0 ? ($profit / $sellingPrice) * 100 : 0;

        return [
            'selling_price' => $sellingPrice,
            'cost' => $cost,
            'profit' => $profit,
            'profit_percent' => $profitPercent,
            'meets_minimum' => $profitPercent >= $this->minimum_profit_percent,
        ];
    }

    /**
     * Get or create pricing rules for a company
     */
    public static function getOrCreateForCompany(int $companyId): self
    {
        return static::firstOrCreate(
            ['company_id' => $companyId],
            [
                'sliding_window_rate' => 500.00,
                'casement_window_rate' => 550.00,
                'sliding_door_rate' => 600.00,
                'hinged_door_rate' => 650.00,
                'other_rate' => 400.00,
                'material_cost_per_m2' => 200.00,
                'labour_cost_per_m2' => 100.00,
                'overhead_cost_per_m2' => 50.00,
                'minimum_profit_percent' => 25.00,
                'tax_rate' => 16.00,
            ]
        );
    }
}
