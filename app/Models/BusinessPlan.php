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
        'tagline',
        'business_stage',
        'industry',
        'industry_size',
        'growth_rate',
        'industry_trends',
        'regulations',
        'technology_changes',
        'country',
        'province',
        'city',
        'website',
        'contact_info',
        'founders',
        'date_established',
        'legal_structure',
        'registration_status',
        'mission_statement',
        'vision_statement',
        'core_values',
        'business_objectives',
        'company_history',
        'long_term_goals',
        'success_factors',
        'background',
        'logo_path',
        'problem_statement',
        'existing_alternatives',
        'why_existing_fail',
        'solution_description',
        'competitive_advantage',
        'swot_strengths',
        'swot_weaknesses',
        'swot_opportunities',
        'swot_threats',
        'customer_pain_points',
        'product_description',
        'delivery_method',
        'product_lifecycle',
        'future_improvements',
        'structured_products',
        'product_features',
        'pricing_strategy',
        'revenue_streams',
        'cost_structure',
        'customer_relationships',
        'channels',
        'key_activities',
        'key_resources',
        'key_partners',
        'business_model_canvas',
        'unique_selling_points',
        'production_process',
        'resource_requirements',
        'target_market_segment',
        'customer_demographics',
        'customer_personas',
        'market_size',
        'surveys_data',
        'interviews_data',
        'competitor_pricing_data',
        'customer_feedback_information',
        'swot_from_research',
        'competitors',
        'structured_competitors',
        'competitive_analysis',
        'marketing_channels',
        'promotion_channels',
        'branding_approach',
        'brand_voice',
        'sales_funnel',
        'sales_channels',
        'customer_retention',
        'sales_process',
        'sales_targets',
        'crm_process',
        'daily_operations',
        'facilities',
        'technology_stack',
        'quality_control',
        'staff_roles',
        'organizational_chart_data',
        'departments_data',
        'key_staff',
        'hiring_plan',
        'recruitment_strategy',
        'employee_benefits',
        'training_plan',
        'performance_management',
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
        'break_even_analysis',
        'funding_requirements',
        'profit_loss_projection',
        'cash_flow_projection',
        'balance_sheet_projection',
        'financial_ratios',
        'scenario_planning_data',
        'risks',
        'structured_risks',
        'mitigation_strategies',
        'timeline',
        'milestones',
        'exit_strategy_type',
        'exit_strategy_details',
        'appendices',
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
        'customer_personas' => 'array',
        'competitors' => 'array',
        'structured_competitors' => 'array',
        'marketing_channels' => 'array',
        'promotion_channels' => 'array',
        'sales_channels' => 'array',
        'staff_roles' => 'array',
        'organizational_chart_data' => 'array',
        'departments_data' => 'array',
        'key_staff' => 'array',
        'equipment_tools' => 'array',
        'supplier_list' => 'array',
        'financial_projections' => 'array',
        'funding_requirements' => 'array',
        'profit_loss_projection' => 'array',
        'cash_flow_projection' => 'array',
        'balance_sheet_projection' => 'array',
        'financial_ratios' => 'array',
        'scenario_planning_data' => 'array',
        'risks' => 'array',
        'structured_risks' => 'array',
        'mitigation_strategies' => 'array',
        'timeline' => 'array',
        'milestones' => 'array',
        'appendices' => 'array',
        'contact_info' => 'array',
        'founders' => 'array',
        'core_values' => 'array',
        'structured_products' => 'array',
        'business_model_canvas' => 'array',
        'date_established' => 'datetime',
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
        return (int) (($this->current_step / 20) * 100);
    }
}
