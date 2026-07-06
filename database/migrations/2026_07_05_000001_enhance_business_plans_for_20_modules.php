<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: skip if already applied (check one key column)
        if (Schema::hasColumn('user_business_plans', 'tagline')) {
            return;
        }

        Schema::table('user_business_plans', function (Blueprint $table) {
            // Module 1: Business Profile — additions
            $table->string('tagline')->nullable()->after('business_name');
            $table->string('business_stage')->nullable()->after('tagline'); // idea, startup, growth, expansion
            $table->string('registration_status')->nullable()->after('legal_structure');
            $table->string('website')->nullable()->after('city');
            $table->json('contact_info')->nullable()->after('website');
            $table->json('founders')->nullable()->after('contact_info');
            $table->date('date_established')->nullable()->after('founders');

            // Module 3: Company Description
            $table->json('core_values')->nullable()->after('vision_statement');
            $table->text('business_objectives')->nullable()->after('core_values');
            $table->text('company_history')->nullable()->after('business_objectives');
            $table->text('long_term_goals')->nullable()->after('company_history');
            $table->text('success_factors')->nullable()->after('long_term_goals');

            // Module 4: Problem Statement — additions
            $table->text('existing_alternatives')->nullable()->after('problem_statement');
            $table->text('why_existing_fail')->nullable()->after('existing_alternatives');

            // Module 5: Products & Services — additions
            $table->text('delivery_method')->nullable()->after('product_description');
            $table->text('product_lifecycle')->nullable()->after('delivery_method');
            $table->text('future_improvements')->nullable()->after('product_lifecycle');
            $table->json('structured_products')->nullable()->after('future_improvements');

            // Module 6: Market Analysis — additions
            $table->string('industry_size')->nullable()->after('industry');
            $table->string('growth_rate')->nullable()->after('industry_size');
            $table->text('industry_trends')->nullable()->after('growth_rate');
            $table->text('regulations')->nullable()->after('industry_trends');
            $table->text('technology_changes')->nullable()->after('regulations');
            $table->json('customer_personas')->nullable()->after('customer_demographics');

            // Module 7: Market Research
            $table->text('surveys_data')->nullable()->after('market_size');
            $table->text('interviews_data')->nullable()->after('surveys_data');
            $table->text('competitor_pricing_data')->nullable()->after('interviews_data');
            $table->text('customer_feedback_information')->nullable()->after('competitor_pricing_data');
            $table->text('swot_from_research')->nullable()->after('customer_feedback_information');

            // Module 8: Competitor Analysis — structured
            $table->json('structured_competitors')->nullable()->after('competitors');

            // Module 9: SWOT
            $table->text('swot_strengths')->nullable()->after('competitive_advantage');
            $table->text('swot_weaknesses')->nullable()->after('swot_strengths');
            $table->text('swot_opportunities')->nullable()->after('swot_weaknesses');
            $table->text('swot_threats')->nullable()->after('swot_opportunities');

            // Module 10: Business Model
            $table->text('revenue_streams')->nullable()->after('pricing_strategy');
            $table->text('cost_structure')->nullable()->after('revenue_streams');
            $table->text('customer_relationships')->nullable()->after('cost_structure');
            $table->text('channels')->nullable()->after('customer_relationships');
            $table->text('key_activities')->nullable()->after('channels');
            $table->text('key_resources')->nullable()->after('key_activities');
            $table->text('key_partners')->nullable()->after('key_resources');
            $table->json('business_model_canvas')->nullable()->after('key_partners');

            // Module 11: Marketing Strategy — additions
            $table->string('brand_voice')->nullable()->after('branding_approach');
            $table->json('promotion_channels')->nullable()->after('marketing_channels');
            $table->text('sales_funnel')->nullable()->after('promotion_channels');

            // Module 12: Sales Strategy
            $table->text('sales_process')->nullable()->after('customer_retention');
            $table->text('sales_targets')->nullable()->after('sales_process');
            $table->text('crm_process')->nullable()->after('sales_targets');

            // Module 13: Operations Plan — additions
            $table->text('facilities')->nullable()->after('daily_operations');
            $table->text('technology_stack')->nullable()->after('facilities');
            $table->text('quality_control')->nullable()->after('technology_stack');

            // Module 14: Organization Structure
            $table->json('organizational_chart_data')->nullable()->after('staff_roles');
            $table->json('departments_data')->nullable()->after('organizational_chart_data');
            $table->json('key_staff')->nullable()->after('departments_data');

            // Module 15: Human Resource Plan
            $table->text('hiring_plan')->nullable()->after('staff_roles');
            $table->text('recruitment_strategy')->nullable()->after('hiring_plan');
            $table->text('employee_benefits')->nullable()->after('recruitment_strategy');
            $table->text('training_plan')->nullable()->after('employee_benefits');
            $table->text('performance_management')->nullable()->after('training_plan');

            // Module 16: Risk Analysis — structured
            $table->json('structured_risks')->nullable()->after('risks');

            // Module 18: Financial Plan — additions
            $table->text('break_even_analysis')->nullable()->after('financial_projections');
            $table->json('funding_requirements')->nullable()->after('break_even_analysis');
            $table->json('profit_loss_projection')->nullable()->after('funding_requirements');
            $table->json('cash_flow_projection')->nullable()->after('profit_loss_projection');
            $table->json('balance_sheet_projection')->nullable()->after('cash_flow_projection');
            $table->json('financial_ratios')->nullable()->after('balance_sheet_projection');
            $table->json('scenario_planning_data')->nullable()->after('financial_ratios');

            // Module 19: Exit Strategy
            $table->string('exit_strategy_type')->nullable()->after('milestones');
            $table->text('exit_strategy_details')->nullable()->after('exit_strategy_type');

            // Module 20: Appendices
            $table->json('appendices')->nullable()->after('exit_strategy_details');
        });
    }

    public function down(): void
    {
        Schema::table('user_business_plans', function (Blueprint $table) {
            $table->dropColumn([
                'tagline', 'business_stage', 'registration_status', 'website', 'contact_info',
                'founders', 'date_established', 'core_values', 'business_objectives',
                'company_history', 'long_term_goals', 'success_factors',
                'existing_alternatives', 'why_existing_fail',
                'delivery_method', 'product_lifecycle', 'future_improvements', 'structured_products',
                'industry_size', 'growth_rate', 'industry_trends', 'regulations', 'technology_changes',
                'customer_personas', 'surveys_data', 'interviews_data', 'competitor_pricing_data',
                'customer_feedback_information', 'swot_from_research', 'structured_competitors',
                'swot_strengths', 'swot_weaknesses', 'swot_opportunities', 'swot_threats',
                'revenue_streams', 'cost_structure', 'customer_relationships', 'channels',
                'key_activities', 'key_resources', 'key_partners', 'business_model_canvas',
                'brand_voice', 'promotion_channels', 'sales_funnel',
                'sales_process', 'sales_targets', 'crm_process',
                'facilities', 'technology_stack', 'quality_control',
                'organizational_chart_data', 'departments_data', 'key_staff',
                'hiring_plan', 'recruitment_strategy', 'employee_benefits', 'training_plan', 'performance_management',
                'structured_risks',
                'break_even_analysis', 'funding_requirements', 'profit_loss_projection',
                'cash_flow_projection', 'balance_sheet_projection', 'financial_ratios', 'scenario_planning_data',
                'exit_strategy_type', 'exit_strategy_details', 'appendices',
            ]);
        });
    }
};
