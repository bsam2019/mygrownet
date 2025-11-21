<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessPlan extends Model
{
    use HasFactory;

    protected $table = 'user_business_plans';

    protected $fillable = [
        'user_id',
        'business_name',
        'industry',
        'country',
        'province',
        'city',
        'legal_structure',
        'mission_statement',
        'vision_statement',
        'background',
        'logo_path',
        'problem_statement',
        'solution_description',
        'competitive_advantage',
        'customer_pain_points',
        'product_description',
        'product_features',
        'pricing_strategy',
        'unique_selling_points',
        'production_process',
        'resource_requirements',
        'target_market_segment',
        'customer_demographics',
        'market_size',
        'competitors',
        'competitive_analysis',
        'marketing_channels',
        'branding_approach',
        'sales_channels',
        'customer_retention',
        'daily_operations',
        'staff_roles',
        'equipment_tools',
        'supplier_list',
        'operational_workflow',
        'startup_costs',
        'monthly_operating_costs',
        'expected_monthly_revenue',
        'price_per_unit',
        'expected_sales_volume',
        'staff_salaries',
        'inventory_costs',
        'utilities_costs',
        'financial_projections',
        'risks',
        'mitigation_strategies',
        'timeline',
        'milestones',
        'theme_color',
        'font_family',
        'template_type',
        'status',
        'current_step',
        'is_premium',
        'completed_at',
        'pdf_path',
    ];

    protected $casts = [
        'startup_costs' => 'decimal:2',
        'monthly_operating_costs' => 'decimal:2',
        'expected_monthly_revenue' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'expected_sales_volume' => 'integer',
        'staff_salaries' => 'decimal:2',
        'inventory_costs' => 'decimal:2',
        'utilities_costs' => 'decimal:2',
        'customer_pain_points' => 'array',
        'product_features' => 'array',
        'resource_requirements' => 'array',
        'customer_demographics' => 'array',
        'competitors' => 'array',
        'marketing_channels' => 'array',
        'sales_channels' => 'array',
        'staff_roles' => 'array',
        'equipment_tools' => 'array',
        'supplier_list' => 'array',
        'financial_projections' => 'array',
        'risks' => 'array',
        'mitigation_strategies' => 'array',
        'timeline' => 'array',
        'milestones' => 'array',
        'current_step' => 'integer',
        'is_premium' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(BusinessPlanExport::class);
    }

    public function aiGenerations(): HasMany
    {
        return $this->hasMany(BusinessPlanAIGeneration::class);
    }

    // Calculated attributes
    public function getMonthlyProfitAttribute(): float
    {
        return $this->expected_monthly_revenue - $this->monthly_operating_costs;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->expected_monthly_revenue == 0) {
            return 0;
        }
        return ($this->monthly_profit / $this->expected_monthly_revenue) * 100;
    }

    public function getBreakEvenMonthsAttribute(): int
    {
        if ($this->monthly_profit <= 0) {
            return 0;
        }
        return (int) ceil($this->startup_costs / $this->monthly_profit);
    }

    public function getCompletionPercentageAttribute(): int
    {
        return (int) (($this->current_step / 10) * 100);
    }
}
