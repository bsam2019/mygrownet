<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\IndustryPresetService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndustryPresetController extends Controller
{
    public function __construct(
        private IndustryPresetService $presetService
    ) {}

    /**
     * Display industry presets selection page
     */
    public function index(): Response
    {
        $presets = $this->presetService->getAllPresets();

        return Inertia::render('CMS/Settings/IndustryPresets', [
            'presets' => $presets,
            'currentIndustry' => auth()->user()->cmsUser?->company?->industry_type,
        ]);
    }

    /**
     * Get preset configuration details
     */
    public function show(string $code)
    {
        $configuration = $this->presetService->getPresetConfiguration($code);

        if (!$configuration) {
            return response()->json(['error' => 'Preset not found'], 404);
        }

        return response()->json($configuration);
    }

    /**
     * Apply preset to current company
     */
    public function apply(Request $request)
    {
        $request->validate([
            'preset_code' => 'required|string|exists:cms_industry_presets,code',
        ]);

        $companyId = auth()->user()->cmsUser?->company_id;

        if (!$companyId) {
            return response()->json(['error' => 'No company found'], 404);
        }

        $success = $this->presetService->applyPresetToCompany(
            $companyId,
            $request->preset_code
        );

        if ($success) {
            return response()->json([
                'message' => 'Industry preset applied successfully',
                'success' => true,
            ]);
        }

        return response()->json(['error' => 'Failed to apply preset'], 500);
    }
}
