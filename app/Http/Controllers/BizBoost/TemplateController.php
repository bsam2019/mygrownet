<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\MarketingService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\TemplateRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateController extends Controller
{
    public function __construct(
        private MarketingService $marketingService,
        private BusinessService $businessService,
        private TemplateRepositoryInterface $templateRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $templates = $this->templateRepo->findByBusiness($business->id);

        return Inertia::render('BizBoost/Templates/Index', [
            'templates' => $templates,
            'categories' => config('modules.bizboost.template_categories', []),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'category' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_featured' => 'boolean',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->createTemplate($business->id, $validated, $request->file('image'));

        return back()->with('success', 'Template created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'category' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_featured' => 'boolean',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->updateTemplate($business->id, $id, $validated, $request->file('image'));

        return back()->with('success', 'Template updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->deleteTemplate($business->id, $id);

        return back()->with('success', 'Template deleted successfully.');
    }
}