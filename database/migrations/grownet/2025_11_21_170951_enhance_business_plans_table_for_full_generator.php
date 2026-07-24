<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_business_plans', function (Blueprint $table) {
            // Step 1: Business Information
            $table->string('industry')->nullable()->after('business_name');
            $table->string('country')->nullable()->after('industry');
            $table->string('province')->nullable()->after('country');
            $table->string('city')->nullable()->after('province');
            $table->string('legal_structure')->nullable()->after('city');
            $table->text('mission_statement')->nullable()->after('legal_structure');
            $table->text('vision_statement')->nullable()->after('mission_statement');
            $table->text('background')->nullable()->after('vision_statement');
            $table->string('logo_path')->nullable()->after('background');
            
            // Step 2: Problem & Solution
            $table->text('problem_statement')->nullable()->after('logo_path');
            $table->text('solution_description')->nullable()->after('problem_statement');
            $table->text('competitive_advantage')->nullable()->after('solution_description');
            $table->json('customer_pain_points')->nullable()->after('competitive_advantage');
            
            // Step 3: Products/Services
            $table->text('product_description')->nullable()->after('customer_pain_points');
            $table->json('product_features')->nullable()->after('product_description');
            $table->text('pricing_strategy')->nullable()->after('product_features');
            $table->text('unique_selling_points')->nullable()->after('pricing_strategy');
            $table->text('production_process')->nullable()->after('unique_selling_points');
            $table->json('resource_requirements')->nullable()->after('production_process');
            
            // Step 4: Market Research
            $table->text('target_market_segment')->nullable()->after('resource_requirements');
            $table->json('customer_demographics')->nullable()->after('target_market_segment');
            $table->text('market_size')->nullable()->after('customer_demographics');
            $table->json('competitors')->nullable()->after('market_size');
            $table->text('competitive_analysis')->nullable()->after('competitors');
            
            // Step 5: Marketing & Sales
            $table->json('marketing_channels')->nullable()->after('competitive_analysis');
            $table->text('branding_approach')->nullable()->after('marketing_channels');
            $table->json('sales_channels')->nullable()->after('branding_approach');
            $table->text('customer_retention')->nullable()->after('sales_channels');
            
            // Step 6: Operations
            $table->text('daily_operations')->nullable()->after('customer_retention');
            $table->json('staff_roles')->nullable()->after('daily_operations');
            $table->json('equipment_tools')->nullable()->after('staff_roles');
            $table->json('supplier_list')->nullable()->after('equipment_tools');
            $table->text('operational_workflow')->nullable()->after('supplier_list');
            
            // Step 7: Financial Plan
            $table->decimal('startup_costs', 15, 2)->nullable()->after('operational_workflow');
            $table->decimal('monthly_operating_costs', 15, 2)->nullable()->after('startup_costs');
            $table->decimal('expected_monthly_revenue', 15, 2)->nullable()->after('monthly_operating_costs');
            $table->decimal('price_per_unit', 15, 2)->nullable()->after('expected_monthly_revenue');
            $table->integer('expected_sales_volume')->nullable()->after('price_per_unit');
            $table->decimal('staff_salaries', 15, 2)->nullable()->after('expected_sales_volume');
            $table->decimal('inventory_costs', 15, 2)->nullable()->after('staff_salaries');
            $table->decimal('utilities_costs', 15, 2)->nullable()->after('inventory_costs');
            $table->json('financial_projections')->nullable()->after('utilities_costs');
            
            // Step 8: Risk Analysis
            $table->json('risks')->nullable()->after('financial_projections');
            $table->json('mitigation_strategies')->nullable()->after('risks');
            
            // Step 9: Implementation Roadmap
            $table->json('timeline')->nullable()->after('mitigation_strategies');
            $table->json('milestones')->nullable()->after('timeline');
            
            // Step 10: Export & Branding
            $table->string('theme_color')->default('#2563eb')->after('milestones');
            $table->string('font_family')->default('Inter')->after('theme_color');
            $table->string('template_type')->nullable()->after('font_family');
            
            // Status & Progress
            $table->integer('current_step')->default(1)->after('template_type');
            $table->string('status')->default('draft')->after('current_step'); // draft, completed, exported
            $table->boolean('is_premium')->default(false)->after('status');
            $table->timestamp('completed_at')->nullable()->after('is_premium');
            
            // Remove old fields that are replaced
            $table->dropColumn(['vision', 'target_market', 'income_goal_6months', 'income_goal_1year', 'team_size_goal', 'marketing_strategy', 'action_plan']);
        });
        
        // Create business_plan_exports table
        Schema::create('business_plan_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_plan_id')->constrained('user_business_plans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('export_type'); // pdf, word, template, pitch_deck
            $table->string('file_path');
            $table->integer('download_count')->default(0);
            $table->boolean('is_premium')->default(false);
            $table->decimal('price_paid', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_plan_exports');
        
        Schema::table('user_business_plans', function (Blueprint $table) {
            // Add back old columns
            $table->text('vision');
            $table->text('target_market');
            $table->decimal('income_goal_6months', 10, 2);
            $table->decimal('income_goal_1year', 10, 2);
            $table->integer('team_size_goal');
            $table->text('marketing_strategy');
            $table->text('action_plan');
            
            // Drop new columns
            $table->dropColumn([
                'industry', 'country', 'province', 'city', 'legal_structure',
                'mission_statement', 'vision_statement', 'background', 'logo_path',
                'problem_statement', 'solution_description', 'competitive_advantage', 'customer_pain_points',
                'product_description', 'product_features', 'pricing_strategy', 'unique_selling_points',
                'production_process', 'resource_requirements', 'target_market_segment', 'customer_demographics',
                'market_size', 'competitors', 'competitive_analysis', 'marketing_channels', 'branding_approach',
                'sales_channels', 'customer_retention', 'daily_operations', 'staff_roles', 'equipment_tools',
                'supplier_list', 'operational_workflow', 'startup_costs', 'monthly_operating_costs',
                'expected_monthly_revenue', 'price_per_unit', 'expected_sales_volume', 'staff_salaries',
                'inventory_costs', 'utilities_costs', 'financial_projections', 'risks', 'mitigation_strategies',
                'timeline', 'milestones', 'theme_color', 'font_family', 'template_type', 'current_step',
                'status', 'is_premium', 'completed_at'
            ]);
        });
    }
};
