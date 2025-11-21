<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Models\BusinessPlan;
use App\Models\BusinessPlanExport;
use App\Services\BusinessPlan\AIGenerationService;
use App\Services\BusinessPlan\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BusinessPlanController extends Controller
{
    public function __construct(
        private AIGenerationService $aiService,
        private ExportService $exportService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get user's existing business plan
        $existingPlan = BusinessPlan::where('user_id', $user->id)
            ->latest()
            ->first();
        
        return Inertia::render('MyGrowNet/Tools/BusinessPlanGenerator', [
            'existingPlan' => $existingPlan,
            'userTier' => $user->starter_kit_tier ?? 'basic',
        ]);
    }

    public function save(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'id' => 'nullable|exists:user_business_plans,id',
            'business_name' => 'required|string|max:255',
            'industry' => 'nullable|string',
            'country' => 'nullable|string',
            'province' => 'nullable|string',
            'city' => 'nullable|string',
            'legal_structure' => 'nullable|in:sole_trader,partnership,company,cooperative',
            'mission_statement' => 'nullable|string',
            'vision_statement' => 'nullable|string',
            'background' => 'nullable|string',
            'problem_statement' => 'nullable|string',
            'solution_description' => 'nullable|string',
            'competitive_advantage' => 'nullable|string',
            'customer_pain_points' => 'nullable|string',
            'product_description' => 'nullable|string',
            'product_features' => 'nullable|string',
            'pricing_strategy' => 'nullable|string',
            'unique_selling_points' => 'nullable|string',
            'production_process' => 'nullable|string',
            'resource_requirements' => 'nullable|string',
            'target_market' => 'nullable|string',
            'customer_demographics' => 'nullable|string',
            'market_size' => 'nullable|string',
            'competitors' => 'nullable|string',
            'competitive_analysis' => 'nullable|string',
            'marketing_channels' => 'nullable|string',
            'branding_approach' => 'nullable|string',
            'sales_channels' => 'nullable|string',
            'customer_retention' => 'nullable|string',
            'daily_operations' => 'nullable|string',
            'staff_roles' => 'nullable|string',
            'equipment_tools' => 'nullable|string',
            'supplier_list' => 'nullable|string',
            'operational_workflow' => 'nullable|string',
            'startup_costs' => 'nullable|numeric|min:0',
            'monthly_operating_costs' => 'nullable|numeric|min:0',
            'expected_monthly_revenue' => 'nullable|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'expected_sales_volume' => 'nullable|integer|min:0',
            'staff_salaries' => 'nullable|numeric|min:0',
            'inventory_costs' => 'nullable|numeric|min:0',
            'utilities_costs' => 'nullable|numeric|min:0',
            'key_risks' => 'nullable|string',
            'mitigation_strategies' => 'nullable|string',
            'timeline' => 'nullable|string',
            'milestones' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'current_step' => 'nullable|integer|min:1|max:10',
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
            ->route('mygrownet.tools.business-plan.view', $plan->id)
            ->with('success', 'Congratulations! Your business plan is complete!');
    }

    public function generateAI(Request $request)
    {
        $user = $request->user();
        
        // Check premium access
        if ($user->starter_kit_tier === 'basic') {
            return back()->with('error', 'AI generation is a premium feature.');
        }

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

            // Save AI generation history
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

    public function export(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'business_plan_id' => 'required|exists:user_business_plans,id',
            'export_type' => 'required|in:template,pdf,word,pitch_deck',
        ]);

        // Check premium access for PDF/Word
        if (in_array($validated['export_type'], ['pdf', 'word', 'pitch_deck']) && $user->starter_kit_tier !== 'premium') {
            return back()->with('error', 'This export format is a premium feature.');
        }

        $plan = BusinessPlan::where('id', $validated['business_plan_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            $filePath = $this->exportService->export($plan, $validated['export_type']);

            // Save export record
            $export = BusinessPlanExport::create([
                'business_plan_id' => $plan->id,
                'user_id' => $user->id,
                'export_type' => $validated['export_type'],
                'file_path' => $filePath,
                'download_count' => 1,
                'last_downloaded_at' => now(),
            ]);

            return back()->with([
                'success' => 'Business plan exported successfully!',
                'downloadUrl' => route('mygrownet.tools.business-plan.download', $export->id),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export business plan. Please try again.');
        }
    }

    public function download(Request $request, int $exportId)
    {
        $user = $request->user();
        
        $export = BusinessPlanExport::where('id', $exportId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Update download count
        $export->increment('download_count');
        $export->update(['last_downloaded_at' => now()]);

        return response()->download(storage_path('app/' . $export->file_path));
    }

    public function view(Request $request, int $planId)
    {
        $user = $request->user();
        
        $plan = BusinessPlan::where('id', $planId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return Inertia::render('MyGrowNet/Tools/BusinessPlanView', [
            'plan' => $plan,
            'userTier' => $user->starter_kit_tier ?? 'basic',
        ]);
    }

    public function list(Request $request)
    {
        $user = $request->user();
        
        $plans = BusinessPlan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('MyGrowNet/Tools/BusinessPlanList', [
            'plans' => $plans,
            'userTier' => $user->starter_kit_tier ?? 'basic',
        ]);
    }
}
