<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\MarketingService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\CustomTemplateRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomTemplateController extends Controller
{
    public function __construct(
        private MarketingService $marketingService,
        private BusinessService $businessService,
        private CustomTemplateRepositoryInterface $customTemplateRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $templates = $this->customTemplateRepo->findByBusiness($business->id);

        return Inertia::render('BizBoost/CustomTemplates/Index', [
            'templates' => $templates,
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
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->createCustomTemplate($business->id, $validated);

        return back()->with('success', 'Custom template created successfully.');
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
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->updateCustomTemplate($business->id, $id, $validated);

        return back()->with('success', 'Custom template updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->marketingService->deleteCustomTemplate($business->id, $id);

        return back()->with('success', 'Custom template deleted successfully.');
    }
}