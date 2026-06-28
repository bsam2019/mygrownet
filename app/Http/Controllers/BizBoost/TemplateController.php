<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostTemplateModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCustomTemplateModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $business = $this->getBusiness($request);
        
        $templates = BizBoostTemplateModel::active()
            ->when($request->category, fn($q, $cat) => $q->byCategory($cat))
            ->when($request->industry, fn($q, $ind) => $q->byIndustry($ind))
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->orderBy('sort_order')
            ->orderByDesc('usage_count')
            ->paginate(24)
            ->withQueryString();

        // Check if user can access premium templates (admins always can)
        $canAccessPremium = $this->subscriptionService->hasFeature($user, 'template_editor', 'bizboost');

        $categories = BizBoostTemplateModel::active()
            ->distinct()
            ->pluck('category');

        $industries = config('modules.bizboost.industry_kits', []);

        return Inertia::render('BizBoost/Templates/Index', [
            'templates' => $templates,
            'categories' => $categories,
            'industries' => $industries,
            'canAccessPremium' => $canAccessPremium,
            'filters' => $request->only(['category', 'industry', 'search']),
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $template = BizBoostTemplateModel::findOrFail($id);
        $user = $request->user();
        
        // Admins can use all templates, others need premium access for premium templates
        $canUse = !$template->is_premium || $this->subscriptionService->hasFeature($user, 'template_editor', 'bizboost');

        return Inertia::render('BizBoost/Templates/Show', [
            'template' => $template,
            'canUse' => $canUse,
        ]);
    }

    public function preview(Request $request, int $id)
    {
        $template = BizBoostTemplateModel::findOrFail($id);
        
        return response()->json([
            'template' => $template,
            'preview_url' => $template->preview_path 
                ? asset('storage/' . $template->preview_path)
                : null,
        ]);
    }

    public function useTemplate(Request $request, int $id)
    {
        $template = BizBoostTemplateModel::findOrFail($id);
        $user = $request->user();
        $business = $this->getBusiness($request);
        
        // Check if premium template (admins can use all templates)
        if ($template->is_premium && !$this->subscriptionService->hasFeature($user, 'template_editor', 'bizboost')) {
            return redirect()->route('bizboost.upgrade')
                ->with('error', 'Premium templates require Professional or Business plan.');
        }

        // Increment usage count
        $template->incrementUsage();

        return redirect()->route('bizboost.posts.create', ['template_id' => $id]);
    }

    public function myTemplates(Request $request): Response
    {
        $business = $this->getBusiness($request);
        
        $customTemplates = BizBoostCustomTemplateModel::where('business_id', $business->id)
            ->with('baseTemplate')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('BizBoost/Templates/MyTemplates', [
            'templates' => $customTemplates,
        ]);
    }

    public function saveCustom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:100',
            'base_template_id' => 'nullable|exists:bizboost_templates,id',
            'template_data' => 'required|array',
            'width' => 'nullable|integer|min:100|max:4000',
            'height' => 'nullable|integer|min:100|max:4000',
        ]);

        $user = $request->user();
        $business = $this->getBusiness($request);

        // Check limit
        $canCreate = $this->subscriptionService->canIncrement($user, 'templates', 'bizboost');
        if (!$canCreate['allowed']) {
            return back()->with('error', $canCreate['reason']);
        }

        $business->customTemplates()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'base_template_id' => $validated['base_template_id'],
            'template_data' => $validated['template_data'],
            'width' => $validated['width'] ?? 1080,
            'height' => $validated['height'] ?? 1080,
        ]);

        return redirect()->route('bizboost.templates.my')
            ->with('success', 'Template saved successfully.');
    }

    public function deleteCustom(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        $template = $business->customTemplates()->findOrFail($id);
        $template->delete();

        return back()->with('success', 'Template deleted successfully.');
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
