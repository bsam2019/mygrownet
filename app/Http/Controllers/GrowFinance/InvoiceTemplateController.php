<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\InvoiceTemplateService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceTemplateModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceTemplateController extends Controller
{
    public function __construct(
        private InvoiceTemplateService $templateService
    ) {}

    /**
     * Display invoice templates
     */
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $templates = $this->templateService->getTemplates($businessId);
        $canCreate = $this->templateService->canCreateTemplate($request->user());

        return Inertia::render('GrowFinance/Templates/Index', [
            'templates' => $templates,
            'canCreate' => $canCreate,
            'layouts' => $this->templateService->getAvailableLayouts(),
        ]);
    }

    /**
     * Show create template form
     */
    public function create(Request $request): Response
    {
        $canCreate = $this->templateService->canCreateTemplate($request->user());

        if (!$canCreate['allowed']) {
            return Inertia::render('GrowFinance/FeatureUpgradeRequired', [
                'feature' => 'Invoice Templates',
                'requiredTier' => 'professional',
                'message' => $canCreate['reason'],
            ]);
        }

        return Inertia::render('GrowFinance/Templates/Create', [
            'layouts' => $this->templateService->getAvailableLayouts(),
            'defaultColors' => GrowFinanceInvoiceTemplateModel::DEFAULT_COLORS,
            'defaultFonts' => GrowFinanceInvoiceTemplateModel::DEFAULT_FONTS,
        ]);
    }

    /**
     * Store new template
     */
    public function store(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $canCreate = $this->templateService->canCreateTemplate($request->user());
        if (!$canCreate['allowed']) {
            return back()->with('error', $canCreate['reason']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'layout' => 'required|in:standard,modern,minimal,professional',
            'colors' => 'nullable|array',
            'colors.primary' => 'nullable|string|max:7',
            'colors.secondary' => 'nullable|string|max:7',
            'colors.accent' => 'nullable|string|max:7',
            'logo_position' => 'nullable|in:left,center,right',
            'show_logo' => 'nullable|boolean',
            'header_text' => 'nullable|string|max:500',
            'footer_text' => 'nullable|string|max:500',
            'terms_text' => 'nullable|string|max:1000',
            'is_default' => 'nullable|boolean',
        ]);

        $this->templateService->createTemplate($businessId, $validated);

        return redirect()->route('growfinance.templates.index')
            ->with('success', 'Template created successfully!');
    }

    /**
     * Show edit template form
     */
    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $template = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Templates/Edit', [
            'template' => $template,
            'layouts' => $this->templateService->getAvailableLayouts(),
            'defaultColors' => GrowFinanceInvoiceTemplateModel::DEFAULT_COLORS,
        ]);
    }

    /**
     * Update template
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'layout' => 'required|in:standard,modern,minimal,professional',
            'colors' => 'nullable|array',
            'logo_position' => 'nullable|in:left,center,right',
            'show_logo' => 'nullable|boolean',
            'header_text' => 'nullable|string|max:500',
            'footer_text' => 'nullable|string|max:500',
            'terms_text' => 'nullable|string|max:1000',
            'is_default' => 'nullable|boolean',
        ]);

        $this->templateService->updateTemplate($businessId, $id, $validated);

        return redirect()->route('growfinance.templates.index')
            ->with('success', 'Template updated!');
    }

    /**
     * Delete template
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $deleted = $this->templateService->deleteTemplate($businessId, $id);

        if (!$deleted) {
            return back()->with('error', 'Cannot delete the only template.');
        }

        return back()->with('success', 'Template deleted.');
    }

    /**
     * Set template as default
     */
    public function setDefault(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $this->templateService->setAsDefault($businessId, $id);

        return back()->with('success', 'Default template updated.');
    }

    /**
     * Preview template
     */
    public function preview(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $template = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->findOrFail($id);

        return Inertia::render('GrowFinance/Templates/Preview', [
            'template' => $template,
        ]);
    }
}
