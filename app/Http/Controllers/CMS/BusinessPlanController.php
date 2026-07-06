<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CMS\Concerns\HasCmsAccess;
use App\Models\BusinessPlan;
use App\Models\BusinessPlanExport;
use App\Services\BusinessPlan\AIGenerationService;
use App\Services\BusinessPlan\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BusinessPlanController extends Controller
{
    use HasCmsAccess;

    public function __construct(
        private AIGenerationService $aiService,
        private ExportService $exportService
    ) {}

    public function index(Request $request)
    {
        $this->getCmsUserOrFail($request);
        $user = $request->user();

        $plans = BusinessPlan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('CMS/BusinessPlans/Index', [
            'plans' => $plans,
            'userTier' => $user->starter_kit_tier ?? 'basic',
        ]);
    }

    public function create(Request $request)
    {
        $this->getCmsUserOrFail($request);
        $user = $request->user();

        $planId = $request->query('plan');
        if ($planId) {
            $existingPlan = BusinessPlan::where('id', $planId)
                ->where('user_id', $user->id)
                ->first();
        } else {
            $existingPlan = BusinessPlan::where('user_id', $user->id)
                ->latest()
                ->first();
        }

        return Inertia::render('CMS/BusinessPlans/Create', [
            'existingPlan' => $existingPlan,
            'userTier' => $user->starter_kit_tier ?? 'basic',
        ]);
    }

    public function save(Request $request)
    {
        $this->getCmsUserOrFail($request);
        $user = $request->user();

        $validated = $request->validate([
            'id' => 'nullable|exists:user_business_plans,id',
            'business_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'business_stage' => 'nullable|in:idea,startup,growth,expansion',
            'industry' => 'nullable|string',
            'industry_size' => 'nullable|string',
            'growth_rate' => 'nullable|string',
            'industry_trends' => 'nullable|string',
            'regulations' => 'nullable|string',
            'technology_changes' => 'nullable|string',
            'country' => 'nullable|string',
            'province' => 'nullable|string',
            'city' => 'nullable|string',
            'website' => 'nullable|url',
            'date_established' => 'nullable|date',
            'legal_structure' => 'nullable|in:sole_trader,partnership,company,cooperative',
            'registration_status' => 'nullable|string',
            'mission_statement' => 'nullable|string',
            'vision_statement' => 'nullable|string',
            'core_values' => 'nullable|json',
            'business_objectives' => 'nullable|string',
            'company_history' => 'nullable|string',
            'long_term_goals' => 'nullable|string',
            'success_factors' => 'nullable|string',
            'background' => 'nullable|string',
            'problem_statement' => 'nullable|string',
            'existing_alternatives' => 'nullable|string',
            'why_existing_fail' => 'nullable|string',
            'solution_description' => 'nullable|string',
            'competitive_advantage' => 'nullable|string',
            'swot_strengths' => 'nullable|string',
            'swot_weaknesses' => 'nullable|string',
            'swot_opportunities' => 'nullable|string',
            'swot_threats' => 'nullable|string',
            'customer_pain_points' => 'nullable|json',
            'product_description' => 'nullable|string',
            'delivery_method' => 'nullable|string',
            'product_lifecycle' => 'nullable|string',
            'future_improvements' => 'nullable|string',
            'structured_products' => 'nullable|json',
            'product_features' => 'nullable|json',
            'pricing_strategy' => 'nullable|string',
            'revenue_streams' => 'nullable|string',
            'cost_structure' => 'nullable|string',
            'customer_relationships' => 'nullable|string',
            'channels' => 'nullable|string',
            'key_activities' => 'nullable|string',
            'key_resources' => 'nullable|string',
            'key_partners' => 'nullable|string',
            'business_model_canvas' => 'nullable|json',
            'unique_selling_points' => 'nullable|string',
            'production_process' => 'nullable|string',
            'resource_requirements' => 'nullable|json',
            'target_market' => 'nullable|string',
            'customer_demographics' => 'nullable|json',
            'customer_personas' => 'nullable|json',
            'market_size' => 'nullable|string',
            'surveys_data' => 'nullable|string',
            'interviews_data' => 'nullable|string',
            'competitor_pricing_data' => 'nullable|string',
            'customer_feedback_information' => 'nullable|string',
            'swot_from_research' => 'nullable|string',
            'competitors' => 'nullable|json',
            'structured_competitors' => 'nullable|json',
            'competitive_analysis' => 'nullable|string',
            'marketing_channels' => 'nullable|json',
            'promotion_channels' => 'nullable|json',
            'branding_approach' => 'nullable|string',
            'brand_voice' => 'nullable|string',
            'sales_funnel' => 'nullable|string',
            'sales_channels' => 'nullable|json',
            'customer_retention' => 'nullable|string',
            'sales_process' => 'nullable|string',
            'sales_targets' => 'nullable|string',
            'crm_process' => 'nullable|string',
            'daily_operations' => 'nullable|string',
            'facilities' => 'nullable|string',
            'technology_stack' => 'nullable|string',
            'quality_control' => 'nullable|string',
            'staff_roles' => 'nullable|json',
            'hiring_plan' => 'nullable|string',
            'recruitment_strategy' => 'nullable|string',
            'employee_benefits' => 'nullable|string',
            'training_plan' => 'nullable|string',
            'performance_management' => 'nullable|string',
            'equipment_tools' => 'nullable|json',
            'supplier_list' => 'nullable|json',
            'operational_workflow' => 'nullable|string',
            'startup_costs' => 'nullable|numeric|min:0',
            'monthly_operating_costs' => 'nullable|numeric|min:0',
            'expected_monthly_revenue' => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'expected_sales_volume' => 'nullable|integer|min:0',
            'staff_salaries' => 'nullable|numeric|min:0',
            'inventory_costs' => 'nullable|numeric|min:0',
            'utilities_costs' => 'nullable|numeric|min:0',
            'financial_projections' => 'nullable|json',
            'break_even_analysis' => 'nullable|string',
            'funding_requirements' => 'nullable|json',
            'profit_loss_projection' => 'nullable|json',
            'cash_flow_projection' => 'nullable|json',
            'balance_sheet_projection' => 'nullable|json',
            'financial_ratios' => 'nullable|json',
            'scenario_planning_data' => 'nullable|json',
            'risks' => 'nullable|json',
            'structured_risks' => 'nullable|json',
            'mitigation_strategies' => 'nullable|json',
            'timeline' => 'nullable|json',
            'milestones' => 'nullable|json',
            'exit_strategy_type' => 'nullable|string',
            'exit_strategy_details' => 'nullable|string',
            'appendices' => 'nullable|json',
            'current_step' => 'nullable|integer|min:1|max:20',
            'status' => 'nullable|in:draft,completed,archived',
        ]);

        if ($request->id) {
            $plan = BusinessPlan::where('id', $request->id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            $plan->update($validated);
        } else {
            $plan = BusinessPlan::create([
                ...$validated,
                'user_id' => $user->id,
                'tier_level' => $user->starter_kit_tier ?? 'free',
            ]);
        }

        return back()->with([
            'success' => 'Business plan saved successfully!',
            'businessPlan' => $plan,
        ]);
    }

    public function complete(Request $request)
    {
        $this->getCmsUserOrFail($request);
        $user = $request->user();

        $validated = $request->validate([
            'id' => 'required|exists:user_business_plans,id',
        ]);

        $plan = BusinessPlan::where('id', $request->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $plan->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('cms.business-plans.show', $plan->id)
            ->with('success', 'Congratulations! Your business plan is complete!');
    }

    public function show(Request $request, int $planId)
    {
        $this->getCmsUserOrFail($request);
        $user = $request->user();

        $plan = BusinessPlan::where('id', $planId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return Inertia::render('CMS/BusinessPlans/Show', [
            'plan' => $plan,
            'userTier' => $user->starter_kit_tier ?? 'basic',
        ]);
    }

    public function generateAI(Request $request)
    {
        $this->getCmsUserOrFail($request);
        $user = $request->user();

        $validated = $request->validate([
            'business_plan_id' => 'nullable|exists:user_business_plans,id',
            'field' => 'required|string',
            'context' => 'required|array',
        ]);

        try {
            $generatedContent = $this->aiService->generate(
                $validated['field'],
                $validated['context']
            );

            if ($validated['business_plan_id']) {
                DB::table('business_plan_ai_generations')->insert([
                    'business_plan_id' => $validated['business_plan_id'],
                    'user_id' => $user->id,
                    'section' => $validated['field'],
                    'prompt' => json_encode($validated['context']),
                    'generated_content' => $generatedContent,
                    'was_accepted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return back()->with([
                'success' => 'Content generated successfully!',
                'generatedContent' => $generatedContent,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate content. Please try again.');
        }
    }

    public function chat(Request $request)
    {
        $this->getCmsUserOrFail($request);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'required|array',
        ]);

        try {
            $result = $this->aiService->chat($validated['message'], $validated['context']);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'chat',
                'content' => 'Sorry, I ran into an error. Please try again.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $this->getCmsUserOrFail($request);
        $user = $request->user();

        $validated = $request->validate([
            'business_plan_id' => 'required|exists:user_business_plans,id',
            'export_type' => 'required|in:template,pdf,word,pitch_deck',
        ]);

        if (in_array($validated['export_type'], ['pdf', 'word', 'pitch_deck']) && $user->starter_kit_tier !== 'premium') {
            abort(403, 'This export format is a premium feature.');
        }

        $plan = BusinessPlan::where('id', $validated['business_plan_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $filePath = $this->exportService->export($plan, $validated['export_type']);
            $fullPath = storage_path('app/' . $filePath);

            if (!file_exists($fullPath)) {
                abort(404, 'Export file not found');
            }

            BusinessPlanExport::create([
                'business_plan_id' => $plan->id,
                'user_id' => $user->id,
                'export_type' => $validated['export_type'],
                'file_path' => $filePath,
                'download_count' => 1,
            ]);

            $extension = match($validated['export_type']) {
                'pdf' => 'pdf',
                'word' => 'rtf',
                'template' => 'html',
                'pitch_deck' => 'html',
            };

            $filename = Str::slug($plan->business_name) . '_business_plan.' . $extension;

            return response()->download($fullPath, $filename);
        } catch (\Exception $e) {
            \Log::error('Business plan export failed: ' . $e->getMessage());
            abort(500, 'Failed to export business plan.');
        }
    }

    public function delete(Request $request, int $planId)
    {
        $this->getCmsUserOrFail($request);
        $user = $request->user();

        $plan = BusinessPlan::where('id', $planId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        BusinessPlanExport::where('business_plan_id', $planId)->delete();
        $plan->delete();

        return redirect()
            ->route('cms.business-plans.index')
            ->with('success', 'Business plan deleted successfully');
    }
}
